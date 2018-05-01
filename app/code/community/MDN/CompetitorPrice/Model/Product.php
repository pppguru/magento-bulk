<?php

class MDN_CompetitorPrice_Model_Product extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('CompetitorPrice/Product');
    }

    public function add($productId, $channel)
    {
        $this->setproduct_id($productId);
        $this->setchannel($channel);
        $this->save();

        Mage::helper('CompetitorPrice')->log('Add to monitoring : product #'.$productId.' and channel '.$channel);

        return $this;
    }

    public function loadByProductChannel($productId, $channel)
    {
        $item = $this->getCollection()->addFieldToFilter('product_id', $productId)->addFieldToFilter('channel', $channel)->getFirstItem();

        if (!$item->getId())
        {
            $item->setproduct_id($productId);
            $item->setchannel($channel);
        }

        return $item;
    }

    public function isMonitored($productId, $channel)
    {
        return ($this->loadByProductChannel($productId, $channel)->getId() > 0);
    }

    public function getOffersAsText()
    {
        $txt = array();

        $details = $this->getDetails();
        if (isset($details['offers']))
        {
            $max = 2;
            foreach($details['offers'] as $offer)
            {
                if (count($txt) < $max) {
                    $txt[] = $offer['competitor'] . ' : ' . $offer['price'];
                }
            }
        }

        return implode(' - ', $txt);
    }

    public function getDetails()
    {
        return json_decode($this->getData('details'), true);
    }

    public function cacheIsValid()
    {
        $frequency = Mage::getStoreConfig('competitorprice/general/frequency');
        $lifeTime = 0;
        switch ($frequency)
        {
            case 'hourly':
                $lifeTime = 3600;
                break;
            case 'daily':
                $lifeTime = 3600 * 24;
                break;
            case 'weekly':
                $lifeTime = 3600 * 24 * 7;
                break;
            case 'monthly':
                $lifeTime = 3600 * 24 * 31;
                break;
        }

        $result = (strtotime($this->getlast_update()) > time() - $lifeTime);

        Mage::helper('CompetitorPrice')->log('Cache for product #'.$this->getproduct_id().' and channel '.$this->getChannel().' is '.($result ? 'valid' : 'expired'));

        return $result;
    }

    public function truncate()
    {
        Mage::getResourceModel('CompetitorPrice/Product')->TruncateTable();
    }
}