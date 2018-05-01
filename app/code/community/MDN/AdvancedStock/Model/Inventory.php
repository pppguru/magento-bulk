<?php

class MDN_AdvancedStock_Model_Inventory extends Mage_Core_Model_Abstract {

    private $_warehouse = null;
    private $_stockPicture = null;

    //status constants

    const kStatusOpened = 'opened';
    const kStatusClosed = 'closed';


    //methods
    const STOCK_TAKE_FULL = 'full';
    const STOCK_TAKE_SUPPLIER = 'supplier';
    const STOCK_TAKE_BRAND = 'brand';
    const STOCK_TAKE_RANDOM = 'random';

    //scan mode
    const STOCK_TAKE_MODE_BY_LOCATION = 'by_location';
    const STOCK_TAKE_MODE_BY_PRODUCT = 'by_product';

    //inventory mode
    const STOCK_TAKE_MODE_COMPLETE = 0;
    const STOCK_TAKE_MODE_PARTIAL = 1;

    /**
     * Constructor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/Inventory');
    }

    /**
     * Return statuses as array
     * @return type 
     */
    public function getStatuses() {
        $options = array();

        $options[self::kStatusOpened] = mage::helper('AdvancedStock')->__(self::kStatusOpened);
        $options[self::kStatusClosed] = mage::helper('AdvancedStock')->__(self::kStatusClosed);

        return $options;
    }

    public function getAdvancedLabel(){
       $label = '';

       if($this->getei_name()){

        $label = $this->getei_name().' on ('.$this->getWarehouse()->getstock_name().' ) ';

        if($this->getei_stock_take_method_code() == self::STOCK_TAKE_FULL){
            $label .= ' '. Mage::helper('AdvancedStock')->__('on all products');
        }
        if($this->getei_stock_take_method_code() == self::STOCK_TAKE_BRAND){

            $brand = '';

            $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
            if($manufacturerCode && $this->getei_stock_take_method_value()){

              $productRessource = Mage::getModel('catalog/product')->getResource();
              $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                              ->setEntityTypeFilter($productRessource->getTypeId())
                              ->addFieldToFilter('attribute_code', $manufacturerCode);
              $attribute = $attributes->getFirstItem()->setEntity($productRessource);
              $manufacturers = $attribute->getSource()->getAllOptions(false);

              foreach ($manufacturers as $manufacturer) {
                  if($manufacturer['value'] == $this->getei_stock_take_method_value() ){
                    $brand = $manufacturer['label'];
                    break;
                  }
              }
            }

            
            $label .=' '. Mage::helper('AdvancedStock')->__('on the manufacturer %s',$brand);
        }

        if($this->getei_stock_take_method_code() == self::STOCK_TAKE_SUPPLIER){
            $supplierName = Mage::getModel('Purchase/Supplier')->load($this->getei_stock_take_method_value())->getsup_name();
            $label .= ' '. Mage::helper('AdvancedStock')->__('on the supplier %s',$supplierName);
        }

        if($this->getei_stock_take_method_code() == self::STOCK_TAKE_RANDOM){
            $label .= ' '. Mage::helper('AdvancedStock')->__('on %s random products',$this->getei_stock_take_method_value());
        }
       }

       return $label;
    }


    /**
     * 
     */
    protected function _beforeSave() {
        parent::_beforeSave();

        //new inventory
        if ($this->getei_date() == ''){
            $this->setei_date(date('Y-m-d H:i:s'));            
        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        $updateStockPictureIsRequired = false;

        //Case 1 : new inventory
        if (!$this->getei_stock_picture_date()){
            $updateStockPictureIsRequired = true;
        }
        
        //Case 2 : mode change ( ex : by brand to By Supplier)        
        if ($this->getei_stock_take_method_code() != $this->getOrigData('ei_stock_take_method_code')) {
            $updateStockPictureIsRequired = true;
        }
        
        //Case 3 : value change (ex : brand X to brand Y)        
        if ($this->getei_stock_take_method_value() != $this->getOrigData('ei_stock_take_method_value')) {
            $updateStockPictureIsRequired = true;
        }


        if($updateStockPictureIsRequired){
            //$this->updateStockPicture();//i don't known why sometime it freeze
        }
    }

    /**
     * Return warehouse
     */
    public function getWarehouse() {
        if ($this->_warehouse == null) {
            $this->_warehouse = Mage::getModel('AdvancedStock/Warehouse')->load($this->getei_warehouse_id());
        }
        return $this->_warehouse;
    }

    /**
     * Return the collection of products (based on stock picture)
     */
    public function getExpectedProducts($location) {

        $locationCondition = '';
        $validRandomIds = array();

        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('name');

        if (($location != MDN_AdvancedStock_Helper_Inventory::TAG_BY_LOCATION) && ($location)){
            $locationCondition = " and eisp_shelf_location = '" . $location . "' ";
        }

        $collection->joinField('eisp_stock', 'AdvancedStock/Inventory_StockPicture', 'eisp_stock', 'eisp_product_id=entity_id', "eisp_inventory_id=" . $this->getId() . $locationCondition, 'inner');

        //limit if no location selected 
        $maxProductDisplayed = Mage::getStoreConfig('advancedstock/stock_take/displayed_product_limit');
        if($maxProductDisplayed && is_numeric($maxProductDisplayed) && $maxProductDisplayed>0)
        {
           $collection->getSelect()->limit($maxProductDisplayed);
        }

        return $collection;
    }

    public function isLocationScanAllowed() {
        $allowed = false;
        if($this->getei_stock_take_mode() != self::STOCK_TAKE_MODE_BY_PRODUCT){
            $allowed = true;
        }
        return $allowed;
    }
    
    /**
     * Return expected quantity for one product (based on stock picture)
     * @param type $productId 
     */
    public function getExpectedQuantityForProduct($productId)
    {
        $item = Mage::getModel('AdvancedStock/Inventory_StockPicture')
                ->getCollection()
                ->addFieldToFilter('eisp_product_id', $productId)
                ->addFieldToFilter('eisp_inventory_id', $this->getId())
                ->getFirstItem();
                
        return $item->geteisp_stock();
    }

    public function getFixedProducts(){


        //same it
            $prefix = Mage::getConfig()->getTablePrefix();
            $sql = 'SELECT distinct eil_sm_id from '.$prefix.'erp_inventory_log WHERE eil_ei_id =  '.$this->getId().';';
            $smIds = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);


        $collection = mage::getModel('AdvancedStock/StockMovement')
                ->getCollection()
                ->addFieldToFilter('sm_id', array('in' => $smIds))
                ->join('catalog/product', 'sm_product_id=`catalog/product`.entity_id')
                ->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id AND store_id = 0 AND attribute_id = ' . mage::getModel('AdvancedStock/Constant')->GetProductNameAttributeId());

        return $collection;
    }

    /**
     * Return scanned products 
     */
    public function getScannedProducts() {
        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('name');

        $collection->joinTable('AdvancedStock/Inventory_Product', 'eip_product_id=entity_id', array('scanned_qty' => 'eip_qty', 'shelf_location' => 'eip_shelf_location'), 'eip_inventory_id=' . $this->getId(), 'inner');
        $collection->joinTable('AdvancedStock/Inventory_StockPicture', 'eisp_product_id=entity_id', array('expected_qty' => 'eisp_stock'), 'eisp_inventory_id=' . $this->getId(), 'left');
        
        return $collection;
    }

    /**
     * Return scanned products
     */
    public function getNotScannedProducts() {

        //get stock picture products not in Inventory_Product
        $inventoryId = $this->getId();

        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'SELECT distinct eisp_product_id ';
        $sql .= ' FROM '.$prefix.'erp_inventory_stock_picture';
        $sql .= ' WHERE eisp_inventory_id = '.$inventoryId;
        $sql .= ' AND eisp_product_id NOT IN ';
        $sql .= ' (SELECT distinct eip_product_id ';
        $sql .= ' FROM '.$prefix.'erp_inventory_product ';
        $sql .= ' WHERE eip_inventory_id = '.$inventoryId.' )';

        $productIds = mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->fetchCol($sql);

        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addFieldToFilter('entity_id', array('in' => $productIds));

        $collection->joinTable('AdvancedStock/Inventory_StockPicture',
                                'eisp_product_id=entity_id',
                                array('eisp_stock' => 'eisp_stock', 'eisp_shelf_location' => 'eisp_shelf_location'),
                                'eisp_inventory_id=' . $inventoryId,
                                'left');

        return $collection;
    }

    /**
     * Return differences between what has been scanned and what is expected 
     */
    public function getDifferences($onlyForScannedLocation = false) {
        
        //get all product ids (from stock picture & from scanned products
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'select distinct eip_product_id from '.$prefix.'erp_inventory_product where eip_inventory_id = '.$this->getId();

        if($this->getei_stock_take_mode() == MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_BY_LOCATION) {
            if ($onlyForScannedLocation == false) {
                $sql .= ' UNION ';
                $sql .= 'select distinct eisp_product_id from ' . $prefix . 'erp_inventory_stock_picture where eisp_inventory_id = ' . $this->getId();
            }
        }
        $allProductIds = mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->fetchCol($sql);

        //get product in picture and scanned with the same quantity
        $sql = "select eip_product_id from ".$prefix."erp_inventory_stock_picture inner join ".$prefix."erp_inventory_product on (eisp_product_id = eip_product_id and eip_inventory_id = eisp_inventory_id) where eip_qty = eisp_stock and eip_inventory_id = ".$this->getId();
        $productsInPictureAndScannedWithSameQuantity = mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->fetchCol($sql);

        //find products with differences
        $productIds = array_diff($allProductIds, $productsInPictureAndScannedWithSameQuantity);
        
        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addFieldToFilter('entity_id', array('in' => $productIds));

        $collection->joinTable('AdvancedStock/Inventory_Product', 
                                'eip_product_id=entity_id', 
                                array('eip_qty' => 'eip_qty', 'eip_shelf_location' => 'eip_shelf_location'), 
                                'eip_inventory_id=' . $this->getId(), 
                                'left');


        $joinMode = ($this->getei_stock_take_mode() == MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_BY_LOCATION)?'left':'inner';


        $collection->joinTable('AdvancedStock/Inventory_StockPicture',
                'eisp_product_id=entity_id',
                array('eisp_stock' => 'eisp_stock', 'eisp_shelf_location' => 'eisp_shelf_location'),
                'eisp_inventory_id=' . $this->getId(),
                $joinMode);

        
        return $collection;
    }
    
    /**
     * return locations in stock picture that has not been scanned 
     */
    public function getMissedLocations()
    {
        $collection = Mage::getModel('AdvancedStock/Inventory_MissedLocation')
                            ->getCollection()
                            ->addFieldToFilter('eisp_inventory_id', $this->getId());
        return $collection;
    }

    /**
     * Add a scanned product
     * @param type $location
     * @param type $productId
     * @param type $qty 
     */
    public function addScannedProduct($location, $productId, $qty) {
        //try to find if a record already exists for this location / product
        $item = $this->getItem($location, $productId);
        if (!$item) {
            $item = Mage::getModel('AdvancedStock/Inventory_Product');
            $item->seteip_inventory_id($this->getId());
            $item->seteip_product_id($productId);
            $item->seteip_shelf_location($location);
        }

        $item->seteip_qty($item->geteip_qty() + $qty);

        $item->save();
    }

    /**
     *
     * @param type $location
     * @param type $productId 
     */
    public function getItem($location, $productId) {
        $item = Mage::getModel('AdvancedStock/Inventory_Product')
                ->getCollection()
                ->addFieldToFilter('eip_inventory_id', $this->getId())
                ->addFieldToFilter('eip_product_id', $productId)
                ->getFirstItem();
        if (!$item->getId())
            return null;
        else
            return $item;
    }

    /**
     * Return true if a location as already been scanned
     * 
     * @param type $location
     * @return boolean 
     */
    public function locationAlreadyScanned($location) {
        $collection = Mage::getModel('AdvancedStock/Inventory_Product')
                ->getCollection()
                ->addFieldToFilter('eip_inventory_id', $this->getId())
                ->addFieldToFilter('eip_shelf_location', $location);
        if ($collection->getSize() > 0)
            return true;
        else
            return false;
    }

    public function inventoryEndAfterFirstCommit(){
        $end = false;
        if($this->getei_stock_take_mode() == self::STOCK_TAKE_MODE_BY_PRODUCT){
          $end = true;
        }
        return $end;
    }

    /**
     * Reset location
     * @param type $location 
     */
    public function resetLocation($location) {
        $collection = Mage::getModel('AdvancedStock/Inventory_Product')
                ->getCollection()
                ->addFieldToFilter('eip_inventory_id', $this->getId())
                ->addFieldToFilter('eip_shelf_location', $location);
        foreach ($collection as $item) {
            $item->delete();
        }
    }

    /**
     * Apply an inventory
     * @param type $stockMovementLabel 
     */
    public function apply($stockMovementLabel, $simulation = false, $onlyForScannedLocation = false) {
        
        $return = '';

        $differences = $this->getDifferences($onlyForScannedLocation);

        if(count($differences)>0){

            $warehouseId = $this->getWarehouse()->getId();
            $warehouseName = $this->getWarehouse()->getstock_name();

            if($simulation){
               $return = $this->applySimulation($differences,$stockMovementLabel,$warehouseId, $warehouseName, $onlyForScannedLocation);
            }else{
               $return = $this->applyUsingGroupTask($differences,$stockMovementLabel,$warehouseId, $warehouseName);
            }
        }

        return $return;
    }

    public function applySimulation($differences,$stockMovementLabel,$warehouseId, $warehouseName, $onlyForScannedLocation){

        $debug = '';
        $simulation = 1;
        $count = 0;
        
        foreach ($differences as $difference) {

            $productId = $difference->getentity_id();
            $qtyScanned = (int)$difference->geteip_qty();
            $qtyInPicture = (int)$difference->geteisp_stock();
            $name = $difference->getName();

            //calculate stock level at inventory date
            if ($qtyScanned != $qtyInPicture) {
                $debug .= $this->applyForProduct($productId, $name, $qtyInPicture, $qtyScanned, $stockMovementLabel, $warehouseId, $warehouseName, $simulation);
                $count++;
            }
        }

        $debug = '<p>Number of stock movement : ' . $count . '</p><p>Partial inventory : '.($onlyForScannedLocation ? ' yes ' : ' no ').'</p>' . $debug;

        return $debug;
    }

    /*
     * Apply stock take by back groud tasks
     */
    public function applyUsingGroupTask($differences,$stockMovementLabel,$warehouseId, $warehouseName){

        $helper = mage::helper('BackgroundTask');
        $taskGroupCode = 'apply_stock_take';
        $redirect = 'adminhtml/AdvancedStock_Inventory/Edit/ei_id/'.$this->getId();
        
        $helper->AddGroup($taskGroupCode,
                          mage::helper('AdvancedStock')->__('Applying Stock take #'.$this->getId()),
                          $redirect);

        $priority = 5;

        foreach ($differences as $difference) {

          $productId = $difference->getentity_id();
          $qtyScanned = (int)$difference->geteip_qty();
          $qtyInPicture = (int)$difference->geteisp_stock();          

          if($productId>0 && $qtyScanned != $qtyInPicture){

            $data = array();
            $data['pid'] = $productId;
            $data['inventory_id'] = $this->getId();
            $data['qty_scanned'] = $qtyScanned;
            $data['qty_picture'] = $qtyInPicture;
            $data['name'] = '';//to avoid any special char issue
            $data['warehouse_id'] = $warehouseId;
            $data['warehouse_name'] = $warehouseName;
            $data['sm_label'] = $stockMovementLabel;

            $helper->AddTask('Apply Stocktake for product #' . $productId,
                           'AdvancedStock/Inventory',
                           'applyStockTakeForProduct',
                           $data,
                           $taskGroupCode, false, $priority);

          }
        }

        //set debug to off to avoid crash
        /*if (Mage::getStoreConfig('advancedstock/cron/debug')){
          Mage::getConfig()->saveConfig('advancedstock/cron/debug', 0);
          Mage::getConfig()->cleanCache();
        }*/

        $helper->ExecuteTaskGroup($taskGroupCode);
       

        //change inventory status
        //if($this->getei_stock_take_partial() != MDN_AdvancedStock_Model_Inventory::STOCK_TAKE_MODE_PARTIAL){
            $this->setei_status(self::kStatusClosed)->save();
        //}

        return $redirect;
       
    }

    /**
     * Apply inventory for one product
     * @param type $productId
     * @param type $qtyInStock
     * @param type $qtyScanned 
     */
    public function applyForProduct($productId, $productName, $qtyInStock, $qtyScanned, $stockMovementLabel, $warehouseId, $warehouseName, $simulation) {

        $stockMovementQty = $qtyInStock - $qtyScanned;
        $targetWarehouseId = null;
        $sourceWarehouseId = null;

        //we need to make a outgoing movement (reduce the stock)
        $fromWarehouseName = Mage::helper('AdvancedStock')->__('nowhere');
        $toWarehouseName = Mage::helper('AdvancedStock')->__('nowhere');
        
        if ($stockMovementQty > 0) {
            $sourceWarehouseId = $warehouseId;
            $fromWarehouseName = $warehouseName;
        } else {
            //we need to make a incoming movement (increase the stock)
            $targetWarehouseId = $warehouseId;
            $stockMovementQty = -$stockMovementQty;
            $toWarehouseName = $warehouseName;
        }

        if (!$simulation) {
            //Create stock movement
            $smDateTime = date('Y-m-d H:i:s');// datetime
            $sm = mage::getModel('AdvancedStock/StockMovement')
                    ->setsm_product_id($productId)
                    ->setsm_qty($stockMovementQty)
                    ->setsm_description($stockMovementLabel)
                    ->setsm_date($smDateTime)
                    ->setsm_type('adjustment')
                    ->setsm_source_stock($sourceWarehouseId)
                    ->setsm_target_stock($targetWarehouseId);
            $sm->save();

            //same it
            $prefix = Mage::getConfig()->getTablePrefix();
            $sql = 'INSERT INTO '.$prefix.'erp_inventory_log VALUES (NULL,'.$this->getId().','.(int)$sm->getsm_id().');';           
            mage::getResourceModel('sales/order_item_collection')->getConnection()->query($sql);

        }

        //return debug
        $debug = '<p>==================================================================</br>';
        $debug .= $productName . ', Product Id = ' . $productId . ', Qty in picture = ' . $qtyInStock . ', Qty scanned = ' . $qtyScanned . '<br>';
        $debug .= 'Create stock movement from ' . $fromWarehouseName . ' to ' . $toWarehouseName . ' with qty = ' . $stockMovementQty;

        return $debug;
    }

    /**
     * Return stock picture
     * @return type 
     */
    public function getStockPicture()
    {
        if ($this->_stockPicture == null)
        {
            $this->_stockPicture = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('name')
                    ->joinTable('AdvancedStock/Inventory_StockPicture', 
                                'eisp_product_id=entity_id', 
                                array('eisp_stock' => 'eisp_stock', 'eisp_shelf_location' => 'eisp_shelf_location', 'eisp_product_id' => 'eisp_product_id'),
                                'eisp_inventory_id=' . $this->getId(), 
                                'inner');

        }
        return $this->_stockPicture;
    }
    
    /**
     *Updae stock picture 
     */
    public function updateStockPicture()
    {        
        $mode = $this->getei_stock_take_method_code();
        $code = $this->getei_stock_take_method_value();
        
        //erase previous records        
        $this->deleteStockPicture();

        //update
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql =  'insert into '.$prefix.'erp_inventory_stock_picture (eisp_inventory_id, eisp_product_id, eisp_stock, eisp_shelf_location)';
        $sql .= ' select '.$this->getId().', csi.product_id, csi.qty, csi.shelf_location ';
        $sql .= ' from '.$prefix.'cataloginventory_stock_item csi';
        
        //restrict to simple products only and existing product by the same time 
        $sql .= ' INNER JOIN '.$prefix.'catalog_product_entity cpe ON (cpe.entity_id=csi.product_id AND cpe.type_id="simple")';
        
        $sqlwhere = ' where csi.stock_id = '.$this->getei_warehouse_id();

        if($mode && $code){
            switch ($mode) {
                case self::STOCK_TAKE_FULL:
                case self::STOCK_TAKE_RANDOM:
                    //nothing
                    break;
                case self::STOCK_TAKE_BRAND:
                     $manufacturerAttributeId = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeId();
                     if($manufacturerAttributeId){
                         $sql .= ' INNER JOIN '.$prefix.'catalog_product_entity_int cpei ON (cpei.entity_id=csi.product_id AND cpei.store_id=0 AND cpei.attribute_id = '.$manufacturerAttributeId.')';
                         $sqlwhere .= ' AND cpei.value='.$code;
                     }
                    break;
                 case self::STOCK_TAKE_SUPPLIER:
                     $sql .= ' INNER JOIN '.$prefix.'purchase_product_supplier pps ON (pps.pps_product_id=csi.product_id)';
                     $sqlwhere .= ' AND pps.pps_supplier_num='.$code;
                    break;
                default:
                    break;
            }
        }

        $sql = $sql.$sqlwhere;
      
        mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->query($sql);

        //if method is random, keep only expected products
        if($this->getei_stock_take_method_code() == self::STOCK_TAKE_RANDOM){

            $collection = $item = Mage::getModel('AdvancedStock/Inventory_StockPicture')
                                                ->getCollection()
                                                ->addFieldToFilter('eisp_inventory_id', $this->getId());
            $totalNumberOfProducts = $this->getei_stock_take_method_value();

            $allProductIds = array();
            foreach($collection as $item)
            {
                $allProductIds[] = $item->geteisp_product_id();
            }

            $randomPositions = array_rand($allProductIds, $totalNumberOfProducts);
            $randomProductIds = array();
            foreach($randomPositions as $randomPosition)
            {
                $randomProductIds[] = $allProductIds[$randomPosition];
            }

            //remove every products that do not belong to $validRandomIds
            foreach($collection as $item)
            {
                if (!in_array($item->geteisp_product_id(), $randomProductIds))
                    $item->delete();
            }
        }

        //store stock picture date
        $this->setei_stock_picture_date(date('Y-m-d H:i:s'))->save();
        
    }


    public function deleteStockPicture(){
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'delete from '.$prefix.'erp_inventory_stock_picture where eisp_inventory_id = '.$this->getId();
        mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->query($sql);
    }
    
    public function deleteScannedProducts(){
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'delete from '.$prefix.'erp_inventory_product where eip_inventory_id = '.$this->getId();
        mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->query($sql);
    }
    
    public function deleteFixedProducts(){
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'delete from '.$prefix.'erp_inventory_log where eil_ei_id = '.$this->getId();
        mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->query($sql);
    }

    public function deleteInventory()
    {        
        $this->deleteStockPicture();

        $this->deleteScannedProducts();
        
        $this->deleteFixedProducts();

        $this->delete();
    }

}
