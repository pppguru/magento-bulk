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
class MDN_AdvancedStock_Helper_Sales_History extends Mage_Core_Helper_Abstract {

    /**
     * Launch update for all products
     */
    public function updateForAllProducts() {
        //create group task
        $taskGroup = 'refresh_sales_history';
        mage::helper('BackgroundTask')->AddGroup($taskGroup,
                mage::helper('SalesOrderPlanning')->__('Refresh Sales History'),
                'adminhtml/system_config/edit/section/advancedstock');

        //get product ids
        $productIds = mage::helper('AdvancedStock/Product_Base')->getProductIds();
        foreach ($productIds as $productId) {
            mage::helper('BackgroundTask')->AddTask('Update sales history for product #' . $productId,
                    'AdvancedStock/Sales_History',
                    'RefreshForOneProduct',
                    $productId,
                    $taskGroup
            );
        }

        //execute task group
        mage::helper('BackgroundTask')->ExecuteTaskGroup($taskGroup);
    }



    public function updateForAllProductsWithoutTask()
    {
        $productIds = mage::helper('AdvancedStock/Product_Base')->getProductIds();
        foreach($productIds as $productId)
        {
            $this->RefreshForOneProduct($productId);
        }
        return count($productIds);
    }

    public function isSalesHistoryInitialized(){
        return (mage::getModel('AdvancedStock/SalesHistory')->getCollection()->getSize()>0);
    }

    /**
     * Launch update for all products
     */
    public function scheduleUpdateForAllProducts() {

        //get product ids
        $productIds = mage::helper('AdvancedStock/Product_Base')->getProductIds();
        foreach ($productIds as $productId) {
            mage::helper('BackgroundTask')->AddTask('Update sales history for product #' . $productId,
                'AdvancedStock/Sales_History',
                'RefreshForOneProduct',
                $productId,
                null,
                false,
                5
            );
        }
    }

    public function scheduleUpdateForRecentSales() {
        $dayCount = 10;
        $productIds = $this->getProductIdsForRecentSales($dayCount);
        foreach ($productIds as $productId) {
            mage::helper('BackgroundTask')->AddTask('Update sales history for product #' . $productId,
                'AdvancedStock/Sales_History',
                'RefreshForOneProduct',
                $productId,
                null,
                false,
                5
            );
        }
    }

    public function getProductIdsForRecentSales($dayCount){
        $dateStartTimestamp = time() - ($dayCount * 24 * 3600);
        $dateStart = date('Y-m-d', $dateStartTimestamp);
        $prefix = Mage::getConfig()->getTablePrefix();

        $sql = "select
					distinct product_id
				from
					" . $prefix . "sales_flat_order_item
				where
					 created_at >= '" . $dateStart . "' ";

        return mage::getResourceModel('catalog/product')->getReadConnection()->fetchCol($sql);
    }


    /**
     * Refresh one sale history
     */
    public function RefreshForOneProduct($productId, $warehouseId = null) {

        $warehouses = Mage::getModel('AdvancedStock/Warehouse')->getCollection();
        if ($warehouseId)
            $warehouses->addFieldToFilter('stock_id', $warehouseId);

        foreach($warehouses as $w)
        {
            $obj = mage::getModel('AdvancedStock/SalesHistory');
            $obj->setsh_product_id($productId);
            $obj->setsh_stock_id($w->getId());

            try {
                //Avoid Multiple entries on each refresh
                $collection = mage::getModel('AdvancedStock/SalesHistory')
                    ->getCollection()
                    ->addFieldToFilter('sh_product_id', $productId)
                    ->addFieldToFilter('sh_stock_id', $w->getId());

                foreach($collection as $saleHistoryEntry){
                    $saleHistoryEntry->delete();
                }

                //this can crash with Integrity constraint violation: 1062 Duplicate entry 'xxx' for key 'sh_product_id'
                $obj->refresh();

                if ($warehouseId)
                    return $obj;

            }catch(Exception $ex){
                Mage::logException($ex);
            }
        }
    }


    /**
     * Return sales history for 1 product
     */
    public function getForOneProduct($productId, $warehouseId, $createIfNotExist = false) {

        $model = mage::getModel('AdvancedStock/SalesHistory')
            ->getCollection()
            ->addFieldToFilter('sh_product_id', $productId)
            ->addFieldToFilter('sh_stock_id', $warehouseId)
            ->getFirstItem();

        if (!$model->getId() && $createIfNotExist) {
            $model = $this->RefreshForOneProduct($productId, $warehouseId);
        }
        return $model;
    }

    /**
     * Return ranges for periods
     */
    public function getRanges() {
        $ranges = array();

        for ($i = 1; $i <= 3; $i++)
            $ranges[] = mage::getStoreConfig('advancedstock/sales_history/period_' . $i);

        return $ranges;
    }

}