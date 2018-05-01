<?php

class MDN_CompetitorPrice_Model_Offers
{

    public function getOffers($products)
    {
        $endPoint = Mage::getStoreConfig('competitorprice/general/host').'offers/';

        $notMonitoredProducts = $this->extractNotMonitoredProducts($products);
        $excludedProducts = $this->extractExcludedProducts($products);
        $inCacheProducts = $this->extractInCacheProducts($products);

        $productsToUpdate = array();
        foreach($products as $productId => $productData)
        {
            if (!isset($notMonitoredProducts[$productId]) && !isset($inCacheProducts[$productId]) && !isset($excludedProducts[$productId]))
                $productsToUpdate[$productId] = $productData;
        }

        foreach($productsToUpdate as $productId => $productData)
            Mage::helper('CompetitorPrice')->log('Request update for #'.$productId.' and channel '.$productData['channel']);

        $params = array('products' => $productsToUpdate);
        $params['uid'] = Mage::getStoreConfig('competitorprice/account/user');
        $params['secret_key'] = Mage::getStoreConfig('competitorprice/account/secret_key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endPoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
        $result = curl_exec($ch);

        $result = json_decode($result, true);


        $this->updateDB($result, $products);    //todo : $products required ONLY because the result from MPM doesnt include channel

        $this->hydrateResultWithUnmonitoredProducts($result, $notMonitoredProducts, $products);
        $this->hydrateResultWithInCacheProducts($result, $inCacheProducts, $products);
        $this->hydrateResultWithInExcludedProducts($result, $excludedProducts, $products);

        return $result;
    }

    protected function extractNotMonitoredProducts($products)
    {
        $notMonitoredProducts = array();
        if (Mage::getStoreConfig('competitorprice/products_to_watch/watch_only_selected')) {
            foreach ($products as $productId => $productData) {
                if (!$this->isMonitored($productId, $productData['channel']))
                    $notMonitoredProducts[$productId] = $productData;
            }
        }
        return $notMonitoredProducts;
    }

    protected function extractInCacheProducts($products)
    {
        $inCacheProducts = array();
        foreach($products as $productId => $productData)
        {
            if ($this->cacheIsValid($productId, $productData['channel'])) {
                $inCacheProducts[$productId] = $productData;
            }
        }
        return $inCacheProducts;
    }

    protected function extractExcludedProducts($products)
    {
        $excludedProducts = array();
        foreach($products as $productId => $productData)
        {
            if ($this->isExcluded($productId))
                $excludedProducts[$productId] = $productData;
        }
        return $excludedProducts;
    }

    protected function isMonitored($productId, $channel)
    {
        return Mage::getSingleton('CompetitorPrice/Product')->isMonitored($productId, $channel);
    }

    protected function isExcluded($productId)
    {
        if (Mage::getStoreConfig('competitorprice/products_to_watch/exclude_disabled_products'))
        {
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getstatus() == 2)
                return true;
        }

        if (Mage::getStoreConfig('competitorprice/products_to_watch/exclude_outofstock_products'))
        {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if(!$stockItem)
                return true;

            if(!$stockItem->ManageStock())
                return true;

            if(Mage::helper('core')->isModuleEnabled('MDN_SalesOrderPlanning')){

                $pas = Mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->getForOneProduct($productId);

                if ($pas->getId() > 0 &&  $pas->getpa_available_qty() == 0){
                    return true;
                }

            }else{
                if ($stockItem->getQty() == 0){
                    return true;
                }
            }
        }

        return false;
    }

    protected function cacheIsValid($productId, $channel)
    {
        $item = Mage::getModel('CompetitorPrice/Product')->loadByProductChannel($productId, $channel);
        if ($item->getId())
            return $item->cacheIsValid();
        else
            false;
    }


    //todo : $products required ONLY because the result from MPM doesnt include channel
    protected function updateDB($result, $products)
    {
        if (isset($result['body']['offers']))
        {
            foreach($result['body']['offers'] as $productId => $productData)
            {
                if ($productData['status'] == 'ASSOCIATED')
                {
                    $channel = $products[$productId]['channel'];

                    Mage::helper('CompetitorPrice')->log('Update cache for product #'.$productId.' and channel '.$channel);

                    $item = Mage::getModel('CompetitorPrice/Product')->loadByProductChannel($productId, $channel);
                    $item->setdetails(json_encode($productData));
                    $item->setlast_update(date('Y-m-d H:i:s'));
                    $item->save();
                }
            }
        }
    }

    protected function hydrateResultWithUnmonitoredProducts(&$result, $notMonitoredProducts, $products)
    {
        foreach($notMonitoredProducts as $productId => $productData)
        {
            $obj = array();
            $obj['status'] = 'NOT_MONITORED';
            $obj['channel'] = $products[$productId]['channel'];
            $obj['ean'] = $products[$productId]['ean'];     //todo : key can be reference later...
            $result['body']['offers'][$productId] = $obj;
        }
    }

    protected function hydrateResultWithInCacheProducts(&$result, $inCacheProducts, $products)
    {
        foreach($inCacheProducts as $productId => $productData)
        {
            $item = Mage::getModel('CompetitorPrice/Product')->loadByProductChannel($productId, $productData['channel']);
            $result['body']['offers'][$productId] = $item->getDetails();
        }
    }

    protected function hydrateResultWithInExcludedProducts(&$result, $excludedProducts, $products)
    {
        foreach($excludedProducts as $productId => $productData)
        {
            $obj = array();
            $obj['status'] = 'EXCLUDED';
            $obj['channel'] = $products[$productId]['channel'];
            $obj['ean'] = $products[$productId]['ean'];     //todo : key can be reference later...
            $result['body']['offers'][$productId] = $obj;
        }
    }

}
