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
class MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus extends Mage_Core_Model_Abstract {

    private $_product = null;
    private $_debug = '';

    //constants for stock status
    const kStatusInStock = 0;
    const kStatusBackInStockAtDate = 1;
    const kStatusAvailableWithinDelay = 2;
    const kStatusOutOfStockWithoutInformation = 3;
    const kStatusOutOfStockAndNotAvailableUntil = 4;
    const kStatusUndefined = 99;

    public function _construct() {
        parent::_construct();
        $this->_init('SalesOrderPlanning/ProductAvailabilityStatus');
    }

    /**
     * Refresh product availability status
     *
     */
    public function Refresh() {
        
        $this->log('##############################################');
        $this->log('Refresh product availanility status for product #'.$this->getpa_product_id());

        //init data
        $this->setpa_available_qty($this->getAvailableQty());
        $this->setpa_supply_delay($this->getSupplyDelay());
        $this->setpa_allow_backorders($this->getAllowBackOrders());
        $this->setpa_backinstock_date($this->getBackInStockDate());

        //out of stock period
        if ($this->outOfStockPeriodIsValid($this->getProduct())) {
            $this->setpa_has_outofstock_period($this->getProduct()->getoutofstock_period_enabled());
            $this->setpa_outofstock_start(($this->getProduct()->getoutofstock_period_from() ? $this->getProduct()->getoutofstock_period_from() : new Zend_Db_Expr('null')));
            $this->setpa_outofstock_end(($this->getProduct()->getoutofstock_period_to() ? $this->getProduct()->getoutofstock_period_to() : new Zend_Db_Expr('null')));
        } else {
            $this->setpa_has_outofstock_period(0);
            $this->setpa_outofstock_start(new Zend_Db_Expr('null'));
            $this->setpa_outofstock_end(new Zend_Db_Expr('null'));
        }

        $this->setpa_is_saleable($this->getIsSaleable()); //last call as all other information must be set

        $this->setpa_status($this->getStatus());

        $this->setpa_debug($this->_debug);

        //Save logs
        $this->storeLogs();
        
        //save
        $this->save();
    }

    /**
     * return available qty
     *
     */
    protected function getAvailableQty() {

        $websiteId = $this->getpa_website_id();
        $productId = $this->getpa_product_id();

        if(Mage::getStoreConfig('planning/general/calculate_for_non_stock_managed_product')) {
            $type = $this->getTypeIdByProductId($productId);

            switch ($type) {
                case 'grouped':
                    $availableQty = $this->getAvailableQtyForGroupedProduct($productId, $websiteId);
                    break;
                case 'configurable':
                    $availableQty = $this->getAvailableQtyForConfigurableProduct($productId, $websiteId);
                    break;
                case 'simple':
                case 'bundle':
                default :
                    $availableQty = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($productId, $websiteId);
                    break;
            }
        }else{
            $availableQty = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($productId, $websiteId);
        }

        $this->log('Check available qty for website id = ' . $websiteId . ' and product id = ' . $productId.' = '.$availableQty);

        return $availableQty;
    }

    public function getAvailableQtyForGroupedProduct($productId, $websiteId){
        $minimumAvailableQty = 0;

        $product = mage::getModel('catalog/product')->load($productId);
        $subProducts = $this->getGroupedSubProductsIds($product);
        $subProductsQty = $this->getGroupedSubProductsQty($product);

        foreach ($subProducts as $subProductId){
            $availableQty = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($subProductId, $websiteId);

            //if one product is missing, the grouped is disabled
            if($availableQty == 0)
                return $availableQty;

            //manage grouped product Qty
            if (array_key_exists($subProductId, $subProductsQty)) {
                $groupedQty = $subProductsQty[$subProductId];
                if ($groupedQty > 0) {

                    //if one product have an available qty < that the grouped qty, the grouped is disabled
                    if ($availableQty < $groupedQty)
                        return 0;

                    $availableQty = (int)($availableQty / $groupedQty);

                }
            }

            //Else, get the smallest availableQty because a grouped availability qty is the smallest value of all his sub products
            $minimumAvailableQty = ($minimumAvailableQty == 0)?$availableQty:((($availableQty <= $minimumAvailableQty))?$availableQty:$minimumAvailableQty);
        }

        return $minimumAvailableQty;
    }

    public function getAvailableQtyForConfigurableProduct($productId, $websiteId){
        $configurableAvailableQty = 0;
        $product = mage::getModel('catalog/product')->load($productId);
        $subProducts = $this->getConfigurableSubProductsIds($product);
        foreach ($subProducts as $subProductId){
            $configurableAvailableQty += mage::helper('AdvancedStock/Product_Base')->getAvailableQty($subProductId, $websiteId);
        }
        return $configurableAvailableQty;
    }

    /**
     * Return supply delay
     *
     */
    protected function getSupplyDelay() {

        //if there is a value at the product level, return this value
        if ($this->getProduct()->getdefault_supply_delay() > 0) {
            $this->log('Supply delay set at product level');
            return $this->getProduct()->getdefault_supply_delay();
        } else {
            //else, retrieve value using default config and suppliers information
            $productId = $this->getpa_product_id();

            //get value from default settings (in system > configuraiton)
            $value = mage::getStoreConfig('purchase/purchase_product/product_default_supply_delay');

            //try to get another value from associated suppliers
            $suppliers = mage::helper('purchase/Product')->getSuppliers($productId);
            $this->log($suppliers->getSize() . ' suppliers found');
            $bestSupplierSupplyDelay = 999;
            foreach ($suppliers as $supplier) {
                $supplierDelay = $supplier->getsup_supply_delay();
                if ($supplier->getpps_quantity_product() > 0)
                    $supplierDelay = $supplier->getsup_shipping_delay();
                if ($supplier->getpps_supply_delay())
                    $supplierDelay = $supplier->getpps_supply_delay();
                if (($supplierDelay < $bestSupplierSupplyDelay) && ($supplierDelay > 0))
                    $bestSupplierSupplyDelay = $supplierDelay;
            }
            if ($bestSupplierSupplyDelay != 999) {
                $this->log('Supply delay found at supplier level');
                $value = $bestSupplierSupplyDelay;
            }
            else
                $this->log('Supply delay found at configuration level');

            return $value;
        }
    }

    /**
     * Define is product allow backorders
     *
     * @return unknown
     */
    protected function getAllowBackOrders() {
        return $this->getProduct()->getStockItem()->getBackorders();
    }

    /**
     * Define if product is saleable
     *
     * @return unknown
     */
    public function getIsSaleable() {

        //if product doesn't manage stock
        if (!$this->getProduct()->getStockItem()->getManageStock())
        {
            return $this->getIsSaleableForNonStockManagedProduct($this->getProduct());
        }
        
        //if product available qty is 0 and backorders set to false, return false
        if ((!$this->getpa_allow_backorders()) && ($this->getpa_available_qty() == 0)) {
            $this->log('No available qty and no back orders');
            return false;
        }

        //if product available qty is 0 and current day is in the "out of stock period", and no end date return false
        if ($this->getpa_has_outofstock_period()) {
            if (($this->getpa_available_qty() == 0) && ($this->getIsWithinOutOfStockPeriod(date('Y-m-d'))) && (!$this->dateIsSet($this->getpa_outofstock_end()))) {
                $this->log('No available qty and within outofstock period');
                return false;
            }
        }

        //if product is set as "out of stock", return false
        if (!$this->getProduct()->getStockItem()->getIsInStock()) {
            if (($this->getpa_available_qty() > 0) && mage::getStoreConfig('advancedstock/general/restore_isinstock'))
            {
                $this->log('Product is available');
                return true;
            }
            else
            {
                $this->log('Product stock status is out of stock');
                return false;
            }
        }
        
        return true;
    }

    /**
     *
     * @param $product
     * @return bool
     */
    public function getIsSaleableForNonStockManagedProduct($product){

        $isSaleable = true;

        if(Mage::getStoreConfig('planning/general/calculate_for_non_stock_managed_product')) {
            switch ($product->gettype_id()) {
                case 'configurable':
                    $isSaleable = $this->getIsSaleableForConfigurableProduct($product);
                    break;
                case 'grouped':
                    $isSaleable = $this->getIsSaleableForGroupedProduct($product);
                    break;
                case 'bundle':
                    $isSaleable = $this->getIsSaleableForBundleProduct($product);
                    break;
            }
        }

        return $isSaleable;
    }

    public function getIsSaleableForGroupedProduct($product){
        $isSaleable = true;

        if ($this->getAvailableQtyForGroupedProduct($product->getId(), 1) == 0){
            return false;
        }

        $subProducts = $this->getGroupedSubProductsIds($product);

        foreach ($subProducts as $subProductId){
            $subProductAvailabilityStatus = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($subProductId, 'pa_product_id');
            if(!$subProductAvailabilityStatus->getIsSaleable()){
                $isSaleable = false;
                break;
            }
        }
        return $isSaleable;
    }


    public function getIsSaleableForConfigurableProduct($product){
        $isSaleable = false;
        $subProducts = $this->getConfigurableSubProductsIds($product);
        foreach ($subProducts as $subProductId){
            $subProductAvailabilityStatus = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($subProductId, 'pa_product_id');
            if($subProductAvailabilityStatus->getIsSaleable()){
                $isSaleable = true;
                break;
            }
        }
        return $isSaleable;
    }

    public function getIsSaleableForBundleProduct($product){
        //TODO
        return true;
    }

    protected function getBundleSubProductsIds($productParent)
    {
        $associatedProducts = $productParent->getTypeInstance(true)->getSelectionsCollection(
            $productParent->getTypeInstance(true)->getOptionsIds($productParent), $productParent
        );
        $items = array();
        foreach($associatedProducts as $p)
        {
            $items[] = $p->product_id;
        }
        return $items;
    }

    /**
     * get the list of product id of the associated products of a Grouped product
     * TODO Enabled disabled ?
     *
     * @param $productParent
     * @return array
     */
    protected function getGroupedSubProductsIds($productParent)
    {
        $associatedProducts = $productParent->getTypeInstance(true)->getAssociatedProducts($productParent);
        $items = array();
        foreach($associatedProducts as $p){
            $items[] = $p->getId();
        }

        return $items;
    }

    /**
     * get the list of product id of the associated products of a Grouped product
     * TODO Enabled disabled ?
     *
     * @param $productParent
     * @return array
     */
    protected function getGroupedSubProductsQty($productParent)
    {
        $associatedProducts = $productParent->getTypeInstance(true)->getAssociatedProducts($productParent);
        $items = array();
        foreach($associatedProducts as $p){
            $items[$p->getId()] = $p->getQty();
        }

        return $items;
    }

    /**
     * get the list of product id of the associated products of a Configurable product
     * TODO Enabled disabled ?
     *
     * @param $productParent
     * @return array
     */
    protected function getConfigurableSubProductsIds($productParent)
    {

        $subProductsIds = array();
        // Get any super_attribute settings we need
        $productAttributesOptions = $productParent->getTypeInstance(true)->getConfigurableOptions($productParent);
        foreach ($productAttributesOptions as $productAttributeOption) {
            foreach ($productAttributeOption as $optionValues) {
                $pId = mage::getModel('catalog/product')->getIdBySku($optionValues['sku']);
                if($pId>0) {
                    $subProductsIds[] = $pId;
                }
            }
        }
        return array_unique($subProductsIds);
    }

    protected function getParentConfigurableProductsIds($childProductId)
    {
        return Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($childProductId);
    }

    protected function getParentGroupedProductsIds($childProductId)
    {
        return Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($childProductId);
    }

    protected function refreshAllPossibleParents(){
        $pid = $this->getpa_product_id();
        $type = $this->getTypeIdByProductId($pid);
        if($type == 'simple') {
            $parentToRefresh = array_merge($this->getParentConfigurableProductsIds($pid), $this->getParentGroupedProductsIds($pid));
            foreach ($parentToRefresh as $parentId) {

                $groupTask = null;
                $skipIfAlreadyPlanned = true;
                $priority = 3;

                mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $parentId . ' (because of child refresh)',
                    'SalesOrderPlanning/ProductAvailabilityStatus',
                    'RefreshForOneProduct',
                    $parentId,
                    $groupTask,
                    $skipIfAlreadyPlanned,
                    $priority
                );
            }
        }
    }

    protected function getTypeIdByProductId($productId){
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = "select type_id from " . $prefix . "catalog_product_entity where entity_id = " . $productId;
        return mage::getResourceModel('cataloginventory/stock_item_collection')->getConnection()->fetchOne($sql);
    }

    /**
     * Return back in stock date
     *
     */
    protected function getBackInStockDate() {
        $expectedQty = $this->getProduct()->getwaiting_for_delivery_qty();
        $expectedDate = $this->getProduct()->getsupply_date();

        $value = new Zend_Db_Expr('null');
        if (($expectedDate) && ($expectedQty > 0)) {
            //if expected date is past, return null
            $expectedDateTimestamp = strtotime($expectedDate);
            $currentDateTimestamp = time();
            if ($currentDateTimestamp > $expectedDateTimestamp)
                return new Zend_Db_Expr('null');

            //Check if expected qty will be available considering pending orders
            $productStock = 0;
            $orderedQty = 0;
            $stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($this->getpa_website_id(),
                            MDN_AdvancedStock_Model_Assignment::_assignmentOrderPreparation,
                            $this->getpa_product_id());
            foreach ($stocks as $stockItem) {
                $productStock += $stockItem->getqty();
                $orderedQty += $stockItem->getstock_ordered_qty();
            }

            if (($productStock + $expectedQty) > $orderedQty) {
                $this->log('Has expected delivery');
                $value = $expectedDate;
            } else {
                $this->log('Has expected delivery but ordered qty is prioritary');
            }
        }
        return $value;
    }

    /**
     * Return associated product
     *
     * @return unknown
     */
    protected function getProduct() {
        if ($this->_product == null) {
            $productId = $this->getpa_product_id();
            $this->_product = mage::getModel('catalog/product')->load($productId);
        }
        return $this->_product;
    }

    /**
     * return out of stock Period information
     *
     * @param unknown_type $html
     * @return unknown
     */
    public function getOutofStockInformation($html = false) {
        $lineBreak = "\n";
        if ($html)
            $lineBreak = '<br>';
        $value = '';
        if ($this->getpa_has_outofstock_period()) {
            $this->log('has out of stock period');
            $value = mage::helper('SalesOrderPlanning')->__('From : %s', mage::helper('core')->formatDate($this->getpa_outofstock_start(), 'short')) . $lineBreak;
            if ($this->getpa_outofstock_end() != '')
                $value .= mage::helper('SalesOrderPlanning')->__('To : %s', mage::helper('core')->formatDate($this->getpa_outofstock_end(), 'short')) . $lineBreak;
        }
        return $value;
    }

    /**
     * Return true if date belongs to the out of stock period
     *
     */
    public function getIsWithinOutOfStockPeriod($date) {
        //init vars
        $dateTimeStamp = strtotime($date);
        $value = false;

        //if has a outofstock period
        if ($this->getpa_has_outofstock_period()) {
            $this->log('Has out of stock period');
            $fromTimeStamp = strtotime($this->getpa_outofstock_start());
            if ($fromTimeStamp < $dateTimeStamp) {
                $this->log('Current date is older than period start date');
                //if end date is set
                if ($this->dateIsSet($this->getpa_outofstock_end())) {
                    $this->log('has out of stock period end date');
                    $toTimeStamp = strtotime($this->getpa_outofstock_end());
                    if ($dateTimeStamp < $toTimeStamp) {
                        $this->log('Current date is earlier than out of stock end date');
                        $value = true;
                    } else {
                        $this->log('Current date is older than out of stock end date');
                    }
                } else {
                    //no end date
                    $this->log('No end date for out of stock period');
                    $value = true;
                }
            }
            else
                $this->log('From date is older than current date');
        }
        else
            $this->log('No out of stock period');


        return $value;
    }

    /**
     * Return message depending of other information
     *
     */
    public function getStatus() {

        //If product doesn't manage stock, return status
        if (!$this->getProduct()->getStockItem()->getManageStock())
        {
            $this->log('Product doesnt manage stock');
            if ($this->getpa_is_saleable())
            {
                $this->log('Product is not saleable');
                return self::kStatusInStock;
            }
            else
            {
                $this->log('Product is not saleable');
                return self::kStatusOutOfStockWithoutInformation;
            }
        }
        
        //message if is saleable
        if ($this->getpa_is_saleable()) {
            $this->log('Is saleable');
            if ($this->getpa_available_qty() > 0) {
                $this->log('Available qty > 0');
                return self::kStatusInStock;
            } else {
                //if has supply date
                if ($this->dateIsSet($this->getpa_backinstock_date())) {
                    $this->log('Has supply date : ' . $this->getpa_backinstock_date());
                    return self::kStatusBackInStockAtDate;
                    //return self::kStatusAvailableWithinDelay;
                    //return mage::helper('SalesOrderPlanning')->__('Back in stock on %s', mage::helper('core')->formatDate($this->getpa_backinstock_date(), 'short'));
                } else {
                    $this->log('No supply date');

                    //if no supply date, check if we are in a outofstock period. If has end date, display it
                    if (($this->getpa_has_outofstock_period()) && ($this->getIsWithinOutOfStockPeriod(date('Y-m-d')))) {
                        $this->log('Has out of stock period');
                        if ($this->dateIsSet($this->getpa_outofstock_end())) {
                            $this->log('Has out of stock period end date');
                            return self::kStatusOutOfStockAndNotAvailableUntil;
                        }
                    }

                    //display msg depending of supply delay
                    $this->log('Available under delay');
                    return self::kStatusAvailableWithinDelay;
                    //return mage::helper('SalesOrderPlanning/ProductAvailability')->getLabel($storeId, $this->getpa_supply_delay());
                }
            }
        } else {
            //product is not salable
            $this->log('Not saleable');

            //if has back in stock date
            if ($this->dateIsSet($this->getpa_backinstock_date())) {
                $this->log('Has supply date : ' . $this->getpa_backinstock_date());
                return self::kStatusBackInStockAtDate;
            }

            //if no out of stock period
            if (!$this->getpa_has_outofstock_period()) {
                $this->log('No out of stock period');
                return self::kStatusOutOfStockWithoutInformation;
                //return mage::helper('SalesOrderPlanning')->__('Out of stock');
            } else {
                //if no end date
                if (!$this->dateIsSet($this->getpa_outofstock_end())) {
                    $this->log('No end date');
                    return self::kStatusOutOfStockWithoutInformation;
                    //return mage::helper('SalesOrderPlanning')->__('Product is currently out of stock. We have no information about availability');
                } else {
                    $this->log('Has end date');
                    return self::kStatusOutOfStockAndNotAvailableUntil;
                    //return mage::helper('SalesOrderPlanning')->__('Product should be available around the %s', mage::helper('core')->formatDate($this->getpa_outofstock_end(), 'short'));
                }
            }
        }

        $this->log('Unable to define status');
        return self::kStatusUndefined;
    }

    /**
     * Return statuses as array
     *
     */
    public function getStatuses() {
        $array = array();

        $array[self::kStatusInStock] = mage::helper('SalesOrderPlanning')->__('In stock');
        $array[self::kStatusAvailableWithinDelay] = mage::helper('SalesOrderPlanning')->__('Available under delay');
        $array[self::kStatusBackInStockAtDate] = mage::helper('SalesOrderPlanning')->__('Back in stock at date');
        $array[self::kStatusOutOfStockAndNotAvailableUntil] = mage::helper('SalesOrderPlanning')->__('Out of stock and not available until');
        $array[self::kStatusOutOfStockWithoutInformation] = mage::helper('SalesOrderPlanning')->__('Out of stock without information');

        $array[self::kStatusUndefined] = mage::helper('SalesOrderPlanning')->__('Undefined');

        return $array;
    }

    /**
     * Log information in debug field
     *
     * @param unknown_type $txt
     */
    protected function log($txt) {
        $this->_debug .= $txt . "\n";
    }
    
    /**
     * Sotre debug var in logs 
     */
    protected function storeLogs()
    {
        mage::log($this->_debug, null, 'erp_product_availability_status.log');
    }

    
    /**
     * Return true if a date is set
     *
     * @param unknown_type $date
     * @return unknown
     */
    private function dateIsSet($date) {
        $value = true;
        if ($date == '')
            $value = false;
        if ($date == new Zend_Db_Expr('null'))
            $value = false;
        return $value;
    }

    /**
     * Return availability message
     *
     */
    public function getMessage() {
        $availabilityMessage = '';
        $productAvailabilityStatus = $this;
        switch ($productAvailabilityStatus->getpa_status()) {
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusAvailableWithinDelay:
                $storeId = $storeId = mage::app()->getStore()->getCode();
                $availabilityMessage = mage::helper('SalesOrderPlanning/ProductAvailabilityRange')->getLabel($storeId, $productAvailabilityStatus->getpa_supply_delay());
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusBackInStockAtDate:
                $availabilityMessage = mage::helper('SalesOrderPlanning')->__('Back in stock at %s', mage::helper('core')->formatDate($productAvailabilityStatus->getpa_backinstock_date(), 'short'));
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusInStock:
                $availabilityMessage = mage::helper('SalesOrderPlanning')->__('In Stock');
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusOutOfStockAndNotAvailableUntil:
                $availabilityMessage = mage::helper('SalesOrderPlanning')->__('Out of stock until %s', mage::helper('core')->formatDate($productAvailabilityStatus->getpa_outofstock_end(), 'short'));
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusOutOfStockWithoutInformation:
                $availabilityMessage = mage::helper('SalesOrderPlanning')->__('Out of Stock');
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusUndefined:
                $availabilityMessage = mage::helper('SalesOrderPlanning')->__('No stock information available');
                break;
        }
        return $availabilityMessage;
    }

    /**
     * Return availability message that can work fro the flag availability on google shopping
     *
     *   Only accepted values by google: in stock, out of stock, preorder ( available for order is deprecated)
     *
     */
    public function getMessageForGoogleShopping() {
        $availabilityMessage = '';
        $productAvailabilityStatus = $this;
        switch ($productAvailabilityStatus->getpa_status()) {
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusAvailableWithinDelay:
                $availabilityMessage = 'preorder';
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusBackInStockAtDate:
                $availabilityMessage = 'preorder';
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusInStock:
                $availabilityMessage = 'in stock';
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusOutOfStockAndNotAvailableUntil:
                $availabilityMessage = 'out of stock';
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusOutOfStockWithoutInformation:
                $availabilityMessage = 'out of stock';
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusUndefined:
                $availabilityMessage = 'out of stock';
                break;
        }
        return $availabilityMessage;
    }

    /**
     * Return estimated date for requested qty
     *
     * @param unknown_type $qty
     */
    public function getEstimatedDateForQty($qty, $baseDateTimeStamp) {
        //if requested qty available, return base date
        if ($qty <= $this->getpa_available_qty())
            return date('Y-m-d', $baseDateTimeStamp);

        //else, try to define date depending of status
        switch ($this->getpa_status()) {
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusInStock: //considering this status means that request status is > available qty
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusAvailableWithinDelay:
                $delay = $this->getpa_supply_delay();
                $finalDateTimestamp = mage::helper('SalesOrderPlanning/Holidays')->addDaysWithoutHolyDays($baseDateTimeStamp, $delay, 1);
                return date('Y-m-d', $finalDateTimestamp);
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusBackInStockAtDate:
                return $this->getpa_backinstock_date();
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusOutOfStockAndNotAvailableUntil:
                return $this->getpa_outofstock_end();
                break;
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusOutOfStockWithoutInformation:
            case MDN_SalesOrderPlanning_Model_ProductAvailabilityStatus::kStatusUndefined:
                return null;
                break;
        }
    }

    /**
     * After save
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        Mage::dispatchEvent('salesorderplanning_productavailabilitystatus_aftersave', array('productavailabilitystatus' => $this));

        //Update sales order planning if required
            if ($this->fieldHasChanged('pa_supply_delay')
                || $this->fieldHasChanged('pa_backinstock_date')
                || $this->fieldHasChanged('pa_outofstock_end')
            ) {
                $productId = $this->getpa_product_id();
                $pendingOrdersIds = mage::helper('AdvancedStock/Sales_PendingOrders')->getPendingOrderIdsForProduct($productId);
                foreach ($pendingOrdersIds as $orderId) {
                    mage::helper('SalesOrderPlanning/Planning')->planPlanningUpdate($orderId);
                }
        }

        if(Mage::getStoreConfig('planning/general/calculate_for_non_stock_managed_product')) {
            if ($this->fieldHasChanged('pa_is_saleable') || $this->fieldHasChanged('pa_available_qty')) {
                $this->refreshAllPossibleParents();
            }
        }
    }

    /**
     * return true if out of stock period is valid
     *
     * @param unknown_type $product
     */
    public function outOfStockPeriodIsValid($product) {

        //if no out of stock period
        if (!$product->getoutofstock_period_enabled())
            return false;

        //if end date is passed
        if ($this->dateIsSet($product->getoutofstock_period_to())) {
            $endDateTimeStamp = strtotime($product->getoutofstock_period_to());
            $currentTimStamp = time();
            if ($endDateTimeStamp < time())
                return false;
        }


        return true;
    }

    /**
     * Apply a fast update on product availability status
     * @param <type> $qty
     */
    public function fastUpdate($qty)
    {
        $this->log('##############################################');
        $this->log('Fast update for product #'.$this->getpa_product_id().' with qty = '.$qty);
        
        $qtyBefore = $this->getpa_available_qty();
        $qtyAfter = $qtyBefore + $qty;  //qty must be negative if called from orders
        if ($qtyAfter < 0)
            $qtyAfter = 0;
        $this->setpa_available_qty($qtyAfter);
        $this->setpa_is_saleable($this->getIsSaleable()); //last call as all other information must be set
        $this->setpa_status($this->getStatus());
        $this->save();
        
        //Save logs
        $this->storeLogs();
        
    }

    /**
     * Method to define il a field value has changed
     *
     * @param unknown_type $fieldname
     * @return unknown
     */
    private function fieldHasChanged($fieldname) {
        if ($this->getData($fieldname) != $this->getOrigData($fieldname))
            return true;
        else
            return false;
    }

}