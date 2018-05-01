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
class MDN_SalesOrderPlanning_Helper_ProductAvailabilityStatus extends Mage_Core_Helper_Abstract {

    const kDefaultWebsiteId = 0;
    
    //to avoid timeout if too many products to be refreshed
    const MAX_PRODUCT_TO_REFRESH_AT_ONCE = 10000;

    /**
     * Refresh all product availability statuses
     *
     */
    public function RefreshAll() {

        //create group task
        $taskGroup = 'refresh_product_availability_status';
        mage::helper('BackgroundTask')->AddGroup($taskGroup, 
            mage::helper('SalesOrderPlanning')->__('Refresh products availability statuses'),
            'adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/Grid'
            );

        //get product ids	
        $productIds = mage::helper('SalesOrderPlanning/Product')->getSimpleProductIds(self::MAX_PRODUCT_TO_REFRESH_AT_ONCE);
        foreach ($productIds as $productId) {
            mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId,
                'SalesOrderPlanning/ProductAvailabilityStatus',
                'RefreshForOneProduct',
                $productId,
                $taskGroup
                );
        }

        //execute task group
        mage::helper('BackgroundTask')->ExecuteTaskGroup($taskGroup);
    }

    public function RefreshOnlyMissing() {

        //create group task
        $taskGroup = 'refresh_product_availability_status';
        mage::helper('BackgroundTask')->AddGroup($taskGroup, 
            mage::helper('SalesOrderPlanning')->__('Refresh missing products availability statuses'),
            'adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/Grid'
            );

        //get missing product ids	
        $productIds = mage::helper('SalesOrderPlanning/Product')->getSimpleProductIds(self::MAX_PRODUCT_TO_REFRESH_AT_ONCE);
        $existingProductAvailabilityStatusProductIds = $this->getProductsWithStatus();
        $missingProductIds = array_diff($productIds, $existingProductAvailabilityStatusProductIds);

        foreach ($missingProductIds as $productId) {
            mage::helper('BackgroundTask')->AddTask('Update missing product availability status for product #' . $productId, 
                'SalesOrderPlanning/ProductAvailabilityStatus',
                'RefreshForOneProduct',
                $productId,
                $taskGroup
                );
        }

        //execute task group
        mage::helper('BackgroundTask')->ExecuteTaskGroup($taskGroup);
    }
    
    /**
    * Create the background tasks to refresh the select products
    *
    */
    public function RefreshOnlySelected($productAvailabilityStatusIds) {

      if(count($productAvailabilityStatusIds)>=1){
        //create group task
        $taskGroup = 'refresh_product_availability_status';
        mage::helper('BackgroundTask')->AddGroup($taskGroup, mage::helper('SalesOrderPlanning')->__('Refresh selected products availability statuses'), 'adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/Grid');

        foreach ($productAvailabilityStatusIds as $paId) {            
            $pa = Mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($paId);            
            $productId = $pa->getpa_product_id();    
            if ($productId){
                mage::helper('BackgroundTask')->AddTask('Update selected product availability status for product #' . $productId, 
                    'SalesOrderPlanning/ProductAvailabilityStatus', 
                    'RefreshForOneProduct',
                    $productId, 
                    $taskGroup
                    );
            }
        }

        //execute task group
        mage::helper('BackgroundTask')->ExecuteTaskGroup($taskGroup);
      }        
    }

    /**
     * Refresh all product availability statuses without using background tasks
     *
     */
    public function RefreshAllWithoutBackgroundTasks() {

        $collection = mage::getModel('catalog/product')
                ->getCollection();

        foreach ($collection as $product) {            
            $this->RefreshForOneProduct($product->getId());
        }

    }

    /**
     * Refresh availability status for product having out of stock period
     *
     */
    public function RefreshForProductWithOutOfStockPeriod() {

        $collection = mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter('outofstock_period_enabled', 1);

        foreach ($collection as $product) {           
            $this->RefreshForOneProduct($product->getId());
        }

    }

    /**
     * Refresh availability status for one product
     *
     * @param unknown_type $productId
     */
    public function RefreshForOneProduct($productId) {
        //load object
        $obj = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($productId, 'pa_product_id');

        //if doesn't exist, create it
        if (!$obj->getId())
            $obj->setpa_product_id($productId)->setpa_website_id(self::kDefaultWebsiteId)->save();

        //refresh datas
        $obj->Refresh();

        return $obj;
    }

    /**
     * Return model for one product, create it if not exist
     *
     * @param unknown_type $productId
     * @param unknown_type $websiteId
     */
    public function getForOneProduct($productId, $websiteId = 0) {
        $productAvailabilityStatus = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($productId, 'pa_product_id');

        if(Mage::getStoreConfig('planning/general/auto_create_if_not_exist')){
            //create it if not exist to get it in all case
            if (!($productAvailabilityStatus->getId())){
                $productAvailabilityStatus = $this->RefreshForOneProduct($productId);
            }
        }

        return $productAvailabilityStatus;
    }

    /**
     * Return ids for products having a product availability status
     */
    protected function getProductsWithStatus() {
        return mage::getResourceModel('SalesOrderPlanning/ProductAvailabilityStatus_Collection')->getProductsWithStatus();
    }

}