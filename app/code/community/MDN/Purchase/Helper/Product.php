<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Helper_Product extends Mage_Core_Helper_Abstract {

    /**
     * Store waiting for delivery qty
     *
     * @param unknown_type $product
     */
    public function updateProductWaitingForDeliveryQty($productId) {
        $value = $this->computeProductWaitingForDeliveryQty($productId);

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); //required, otherwise magento bugs on oridata collection
        $product = mage::getModel('catalog/product')->load($productId);

        if ($value != $product->getwaiting_for_delivery_qty()) {
            if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
              Mage::getModel('catalog/Resource_Product_Action')->updateAttributes(array($productId), array('waiting_for_delivery_qty' => $value), 0);
            }else{
              Mage::getSingleton('catalog/product_action')->updateAttributes(array($productId), array('waiting_for_delivery_qty' => $value), 0);
            }

            Mage::dispatchEvent('product_waiting_for_delivery_qty_change', array('product_id' => $productId,
                        'old_value' => $product->getwaiting_for_delivery_qty(),
                        'new_value' => $value));
        }
    }

    /**
     * Compute waiting for delivery qty
     */
    public function computeProductWaitingForDeliveryQty($productId) {
        $tableprefix =  mage::getModel('Purchase/Constant')->getTablePrefix();

        $sql = "
			SELECT sum( if(((pop_qty - pop_supplied_qty)>0), (pop_qty - pop_supplied_qty), 0) )
			FROM " .$tableprefix. "purchase_order, " . $tableprefix . "purchase_order_product
			WHERE po_num  = pop_order_num
			AND po_status = '" . MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY . "'
			AND pop_product_id = " . $productId . " ";

        //get result
        $value = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
        if ($value == '')
            $value = 0;

        //convert result in sales unit if packaging enabled
        if (mage::helper('purchase/Product_Packaging')->isEnabled()) {
            $value = mage::helper('purchase/Product_Packaging')->convertToSalesUnit($productId, $value);
        }

        return $value;
    }

    /**
     * Store product next supply date
     *
     * @param unknown_type $productId
     */
    public function updateProductDeliveryDate($productId) {
        $deliveryDate = $this->computeProductDeliveryDate($productId);

        //update product
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); //required, otherwise magento bugs on oridata collection
        $product = mage::getModel('catalog/product')->load($productId);
        if ($deliveryDate != $product->getsupply_date()) {
            if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
              Mage::getSingleton('catalog/Resource_Product_Action')->updateAttributes(array($productId), array('supply_date' => $deliveryDate), 0);
            }else{
              Mage::getSingleton('catalog/product_action')->updateAttributes(array($productId), array('supply_date' => $deliveryDate), 0);
            }
            Mage::dispatchEvent('product_delivery_date_change', array('product_id' => $productId,
                        'old_value' => $product->getsupply_date(),
                        'new_value' => $deliveryDate));
        }
    }

    /**
     * Compute product delivery date
     *
     * @param unknown_type $productId
     * @return unknown
     */
    public function computeProductDeliveryDate($productId) {
        $deliveryDate = null;

        //collect PO for product (po status = waiting for delivery and order contains product
        $collection = mage::getModel('Purchase/Order')
                        ->getCollection()
                        ->join('Purchase/OrderProduct', 'po_num=pop_order_num')
                        ->addFieldToFilter('po_status', MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY)
                        ->addFieldToFilter('pop_product_id', $productId);


        //browse colleciton to set date
        foreach ($collection as $item) {
            if ($item->getpop_qty() > $item->getpop_supplied_qty()) {

                //calculate item delivery date

                //first take the Purchase order delivery date
                $itemDeliveryDate = $item->getpo_supply_date();

                //if there is a delivery_date at product level (if option is activated)
                if (mage::getStoreConfig('purchase/purchase_product_grid/display_specific_delivery_date')) {
                    if ($item->getpop_delivery_date()){
                        $itemDeliveryDate = $item->getpop_delivery_date();
                    }
                }

                //apply if we can get  best delivery date
                if (($deliveryDate == null) || ($deliveryDate > $itemDeliveryDate))
                    $deliveryDate = $itemDeliveryDate;
            }
        }

        return $deliveryDate;
    }

    /**
     * Store product cost
     *
     * @param unknown_type $cost
     */
    public function updateProductCost($productId) {
        //Doesnt compute cost if not enabled
        if (mage::getStoreConfig('purchase/purchase_order/store_product_cost') != 1)
            return;

        //init vars
        $debug = '##Compute cost for product #' . $productId;
        $cost = 0;
        $stock = 0;

        //compute product qty in stock
        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
        foreach ($stocks as $item)
            $stock += $item->getqty();
        $priceSum = 0;
        $priceCount = 0;
        $debug .= ' (' . $stock . ' units in whole stocks)';

        //collect stock movement
        $collection = mage::getModel('AdvancedStock/StockMovement')
                        ->getCollection()
                        ->addFieldToFilter('sm_product_id', $productId)
                        ->addFieldToFilter('sm_po_num', array('gt' => 0))
                        ->setOrder('sm_id', 'desc');
        foreach ($collection as $sm) {
            //define qty to use
            $consideredQty = $stock;
            if ($consideredQty > $sm->getsm_qty())
                $consideredQty = $sm->getsm_qty();

            //retrieve PO item
            $poItem = null;
            $orderId = $sm->getsm_po_num();
            $poItemCollection = mage::getModel('Purchase/OrderProduct')
                            ->getCollection()
                            ->addFieldToFilter('pop_product_id', $productId)
                            ->addFieldToFilter('pop_order_num', $orderId);
            foreach ($poItemCollection as $item) {
                $poItem = $item;
                break;
            }

            //add product prices
            if ($poItem != null) {
                $unitPriceWithECost = $poItem->getUnitPriceWithExtendedCosts_base();
                if ($unitPriceWithECost > 0) {
                    $priceSum += $consideredQty * $unitPriceWithECost;
                    $priceCount += $consideredQty;
                    $debug .= ' (consider ' . $consideredQty . ' coming from PO # ' . $sm->getsm_po_num() . ' with cost = ' . $unitPriceWithECost . ' ) ';

                    //decrease stock
                    $stock -= $consideredQty;
                }
            }
        }

        //compute cost
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);  //required, otherwise magento bugs on oridata collection
        $cost = 0;
        if ($priceCount > 0)
            $cost = $priceSum / $priceCount;

        //if cost not calculated, try to calculate it from supplier prices
        //todo : use magento objects instead of direct sql
        if ($cost == 0) {
            $cost = mage::getResourceModel('Purchase/ProductSupplier')->getAverageCost($productId);

            $sql = "
					select
						avg(pps_last_price)
					from
						" . mage::getModel('Purchase/Constant')->getTablePrefix() . "purchase_product_supplier
					where
						pps_last_price > 0
						and pps_product_id = " . $productId . "
					";
            $cost = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
        }

        //store cost
        if ($cost > 0) {

            if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
              Mage::getModel('catalog/Resource_Product_Action')
                      ->updateAttributes(array($productId), array('cost' => $cost), 0);
            }else{
              Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array($productId), array('cost' => $cost), 0);
            }

            

            Mage::dispatchEvent('product_cost_change', array('product_id' => $productId, 'new_value' => $cost));
        }

        $debug .= ' (pricesum = ' . $priceSum . ', pricecount=' . $priceCount . ') ';
        $debug .= ' (final cost = ' . $cost . ') ';
        //mage::log($debug);
    }

    /**
     * Return suppliers for 1 product
     *
     * @param unknown_type $productId
     */
    public function getSuppliers($productId) {
        $collection = mage::GetModel('Purchase/ProductSupplier')
                        ->getCollection()
                        ->join('Purchase/Supplier',
                                'sup_id=pps_supplier_num')
                        ->addFieldToFilter('pps_product_id', $productId)
                        ->setOrder('pps_last_order_date', 'desc')
        ;
        return $collection;
    }


    const allowedLineCountForImportWithoutPrice = 2;
    const allowedLineCountForImport = 3;
    const skuPositionForImport = 0;
    const qtyPositionForImport = 1;
    const pricePositionForImport = 2;


    public function importProducts($lines, $poId, $delimiterKeyCode){

        $productAddedCount = 0;
        $lineCount = 0;
        $log = '';        

        if($poId>0){

            $delimiter = chr($delimiterKeyCode);

            $po = mage::getModel('Purchase/Order')->load($poId);

            if($po && $po->getId()>0){

		$supplierId = $po->getpo_sup_num();
		
                foreach ($lines as $line){

                    $lineCount++;

                    //skip any empty line
                    $line = trim($line);
                    if(strlen($line) == 0){
                       $log .= mage::helper('purchase')->__('Line %s is empty : skipped',$lineCount).'<br/>';
                       continue;
                    }

                    //Skip the csv header if it is really the correct header
                    if(($lineCount == 1) && (strpos(strtolower($line), 'sku') !== FALSE) && (strpos(strtolower($line), 'qty') !== FALSE)){
                        $log .= mage::helper('purchase')->__('Line %s : Header found : skipped',$lineCount).'<br/>';
                        continue;
                    };
                        

                     //clean useless separators
                    $line = str_replace('"', "", $line);
                    $line = str_replace("'", "", $line);

                    //split datas
                    $rowArray = explode($delimiter,trim($line));

                    //Check line consistancy and skip if no import possible
                    $columnCount =  count($rowArray);
                    if(!($columnCount == self::allowedLineCountForImport || $columnCount == self::allowedLineCountForImportWithoutPrice)){
                      $log .= mage::helper('purchase')->__('Line %s is invalid : column count = %s, expected is %s or %s',$lineCount,$columnCount,self::allowedLineCountForImport,self::allowedLineCountForImportWithoutPrice);
                      if(strpos($line, $delimiter) === FALSE){
                          $log .= ' : '.mage::helper('purchase')->__('Delimiter %s is not present',$delimiter);
                      }
                      $log .= '<br/>';
                      continue;
                    }

                    
                    
                    $sku = $rowArray[self::skuPositionForImport];
                    $qty = $rowArray[self::qtyPositionForImport];

                    //price column is optionnal
                    $price = null;
                    if($columnCount == self::allowedLineCountForImport)
                        $price = $rowArray[self::pricePositionForImport];

                    $productId = mage::getModel('catalog/product')->getIdBySku($sku);

		    //Alternatively, try to find ProductId By Supplier Sku
                    if(!$productId){
                        $productId = mage::getModel('purchase/ProductSupplier')->getProductIdFromSupplierSku($sku,$supplierId);
                    }

                    if($productId > 0 && $qty > 0){

                        $pop = $po->AddProduct($productId,$qty);

                        if($price){
                            $pop->setpop_price_ht($price)->setpop_price_ht_base($price)->save();
                        }
                        
                        $productAddedCount++;

                    }else{
                        $log .= mage::helper('purchase')->__('Sku or Qty is invalid for SKU=%s Qty=%s for line=%s',$sku,$qty,$lineCount).'<br/>';
                    }
                }
            }

        }else{
            throw new Exception(mage::helper('purchase')->__('Purchase order Id %s is incorrect',$poId));
        }

        $log .= mage::helper('purchase')->__('%s product added / %s lines processed',$productAddedCount,$lineCount).'<br/>';

        return $log;
    }

}
