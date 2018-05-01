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
class MDN_AdvancedStock_Helper_Product_Base extends Mage_Core_Helper_Abstract {

    /**
     * Return stocks collection (for every warehouse)
     *
     * @param unknown_type $productId
     */
    public function getStocks($productId) {
        $collection = mage::getModel('cataloginventory/stock_item')
                        ->getCollection()
                        ->join('AdvancedStock/Warehouse', 'main_table.stock_id=`AdvancedStock/Warehouse`.stock_id')
                        ->addFieldToFilter('product_id', $productId)
        ;

        return $collection;
    }

    /**
     * Return non Hidden stocks collection
     *
     * @param int $productId
     */
    public function getStocksToDisplay($productId) {
        return mage::getModel('cataloginventory/stock_item')
            ->getCollection()
            ->join('AdvancedStock/Warehouse', 'main_table.stock_id=`AdvancedStock/Warehouse`.stock_id AND `AdvancedStock/Warehouse`.stock_is_hidden=0')
            ->addFieldToFilter('product_id', $productId);
    }

    /**
     * Return stocks assigned for a website
     *
     * @param int $websiteId
     * @param string $assignmentType
     * @param int $productId
     */
    public function getStocksForWebsiteAssignment($websiteId, $assignmentType, $productId) {
        $collection = mage::getModel('cataloginventory/stock_item')
                        ->getCollection()
                        ->join('AdvancedStock/Assignment', 'main_table.stock_id=csa_stock_id')
                        ->join('AdvancedStock/Warehouse', 'main_table.stock_id=`AdvancedStock/Warehouse`.stock_id')
                        ->addFieldToFilter('csa_website_id', $websiteId)
                        ->addFieldToFilter('csa_assignment', $assignmentType)
                        ->addFieldToFilter('product_id', $productId);

        return $collection;
    }




    /**
     * Update product stocks, ordered qty, reserved qty ...
     *
     * @param unknown_type $product
     */
    public function updateStocks($product) {
        //update data for each stocks
        $stocks = $this->getStocks($product->getId());
        foreach ($stocks as $stock) {
            //if product stock doesn't manage stock, continue
            if ($stock->ManageStock()) {

                $saveCount = 0;

                //update qty
                $saveCount += $stock->storeQty();

                //update reservation
                $saveCount += mage::helper('AdvancedStock/Product_Reservation')->reserveProductForPendingOrders($product->getId());

                //update reserved qty
                $saveCount += mage::helper('AdvancedStock/Product_Reservation')->storeReservedQtyForStock($stock, $product->getId());

                //update ordered qty
                $saveCount += mage::helper('AdvancedStock/Product_Ordered')->storeOrderedQtyForStock($stock, $product->getId());

                //force to recalculate is in stock for product
                if($saveCount == 0){
                    $stock->save();
                }
            }
        }
    }

    /**
     * Update stocks using product Id
     *
     * @param unknown_type $productId
     */
    public function updateStocksFromProductId($productId) {
        $product = mage::getModel('catalog/product')->load($productId);
        $this->updateStocks($product);
    }

    /**
     * Plan product update stocks using background tasks
     *
     * @param unknown_type $productId
     */
    public function planUpdateStocksWithBackgroundTask($productId, $reason) {
        mage::helper('BackgroundTask')->AddTask('Update stocks for product #' . $productId.' ('.$reason.')',
                'AdvancedStock/Product_Base',
                'updateStocksFromProductId',
                $productId
        );
    }

    /**
     * Return pending sales order for product
     *
     * @param unknown_type $product
     */
    public function getPendingOrders($productId, $asArray = false) {
        $OrdersId = mage::helper('AdvancedStock/Sales_PendingOrders')->getPendingOrderIdsForProduct($productId);

        //collect orders
        if (mage::helper('AdvancedStock/FlatOrder')->ordersUseEavModel()) {
            $collection = mage::getModel('sales/order')
                            ->getCollection()
                            ->addFieldToFilter('entity_id', array('in' => $OrdersId))
                            ->addAttributeToSelect('status')
                            ->addAttributeToSelect('state')
                            ->addAttributeToSelect('total_paid')
                            ->addAttributeToSelect('grand_total')
                            ->addAttributeToSelect('payment_validated')
                            ->addAttributeToSelect('customer_firstname')
                            ->addAttributeToSelect('customer_lastname')
                            ->addExpressionAttributeToSelect('billing_name',
                                    'CONCAT({{customer_firstname}}, " ", {{customer_lastname}}, " ")',
                                    array('customer_firstname', 'customer_lastname'))
                            ->setOrder('entity_id', 'asc')
            ;
        } else {
            $collection = mage::getModel('sales/order')
                            ->getCollection()
                            ->addFieldToFilter('main_table.entity_id', array('in' => $OrdersId))
                            ->join('sales/order_address', '`sales/order_address`.entity_id=billing_address_id', array('billing_name' => "concat(firstname, ' ', lastname)"));
        }

        //return datas
        if ($asArray) {
            $orders = array();
            foreach ($collection as $order)
                $orders[] = $order;
            return $orders;
        }
        else
            return $collection;
    }

    /**
     * Return available qty for sale for a website
     *
     * @param unknown_type $productId
     * @param unknown_type $websiteId
     */
    public function getAvailableQty($productId, $websiteId) {
        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales, $productId);
        $value = 0;
        $stockLevel = 0;
        $orderedQty = 0;
        foreach ($stocks as $stock) {
            $stockLevel += $stock->getqty();
            $orderedQty += $stock->getstock_ordered_qty();
        }

        $value = $stockLevel - $orderedQty;
        if ($value < 0)
            $value = 0;

        return $value;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $stocks
     */
    public function ManageStock($productId, $stocks = null) {
        //init stocks if null
        if ($stocks == null) {
            $stocks = $this->getStocks($productId);
        }

        foreach ($stocks as $stock) {
            return $stock->ManageStock();
        }

        return false;
    }

    /**
     * Force product reindex
     * @param <type> $productId
     */
    public function reindex($productId) {
        //force product reindex
        $product = mage::getModel('catalog/product')->load($productId);
        if ($product->getId() > 0)
        {
            $product->setForceReindexRequired(true);
            $product->save();
        }
    }

    /**
     * Return all product ids
     */
    public function getProductIds() {

        $select = mage::getResourceModel('catalog/product')
                        ->getReadConnection()
                        ->select()
                        ->from(mage::getResourceModel('catalog/product')->getTable('catalog/product'))
                        ->order('entity_id ASC');

        $productIds = mage::getResourceModel('catalog/product')->getReadConnection()->fetchCol($select);

        return $productIds;
    }

    /**
     * Get the list of product manufacturer if the mnuafacturer exists
     * mage::helper('AdvancedStock/Product_Base')->getManufacturerListForFilter()
     *
     * @return type
     */
    public function getManufacturerListForFilter(){

        $list = array();

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();

        if($manufacturerCode){

          $productRessource = Mage::getModel('catalog/product')->getResource();

          $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                          ->setEntityTypeFilter($productRessource->getTypeId())
                          ->addFieldToFilter('attribute_code', $manufacturerCode);

          $attribute = $attributes->getFirstItem()->setEntity($productRessource);
          $manufacturers = $attribute->getSource()->getAllOptions(false);

          foreach ($manufacturers as $manufacturer) {
              $list[$manufacturer['value']] = $manufacturer['label'];
          }

        }

        return $list;
    }

    public function productExists($productId)
    {
        if (!$productId)
            return false;

        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'select sku from '.$prefix.'catalog_product_entity where entity_id = '.$productId;
        $sku = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
        return (strlen($sku) > 0);
    }

}