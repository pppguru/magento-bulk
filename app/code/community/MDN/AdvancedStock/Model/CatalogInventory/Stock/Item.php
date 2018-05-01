<?php

class MDN_AdvancedStock_Model_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {

    private $_availableQty = null;
    private $_defaultWarehouseId = 1;

    /**
     * Return stock for product / warehouse
     *
     * @param unknown_type $productId
     * @param unknown_type $warehouseId
     * @return unknown
     */
    public function loadByProductWarehouse($productId, $warehouseId) {
        $item = $this->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('stock_id', $warehouseId)
                ->getFirstItem();
        if ($item->getId())
            return $item;
        else
            return null;
    }

    //***************************************************************************************************************************************************
    //***************************************************************************************************************************************************
    // STATUSES
    //***************************************************************************************************************************************************
    //***************************************************************************************************************************************************
    //stock status

    const _StatusSalesOrder = 'sales_order';
    const _StatusQtyMini = 'min_qty';
    const _StatusOk = 'ok';
    const _StatusPendingSupply = 'pending_supply';
    const _StatusOther = 'other';

    /**
     * Return statuses
     *
     */
    public function getStatuses() {
        $retour = array();
        $retour[self::_StatusSalesOrder] = mage::helper('AdvancedStock')->__(self::_StatusSalesOrder);
        $retour[self::_StatusOk] = mage::helper('AdvancedStock')->__(self::_StatusOk);
        $retour[self::_StatusQtyMini] = mage::helper('AdvancedStock')->__(self::_StatusQtyMini);
        $retour[self::_StatusPendingSupply] = mage::helper('AdvancedStock')->__(self::_StatusPendingSupply);
        $retour[self::_StatusOther] = mage::helper('AdvancedStock')->__(self::_StatusOther);

        return $retour;
    }

    /**
     * Return stock status
     *
     */
    public function getStatus() {
        if ($this->getNeededQty() == 0) {
            return self::_StatusOk;
        } else {
            if ($this->getstock_ordered_qty() > $this->getqty())
                return self::_StatusSalesOrder;
            else
                return self::_StatusQtyMini;
        }
    }

    //***************************************************************************************************************************************************
    //***************************************************************************************************************************************************
    // OVERLOADS
    //***************************************************************************************************************************************************
    //***************************************************************************************************************************************************

    /**
     * Return website id
     *
     */
    protected function getWebsiteId() {
        $websiteId = Mage::app()->getStore()->getwebsite_id();
        return $websiteId;
    }

    /**
     * Check quantity
     *
     * @param   decimal $qty
     * @exception Mage_Core_Exception
     * @return  bool
     */
    public function checkQty($qty) {

        $qtyAvailableForSales = $this->getAvailableQtyForSale();

        //try to load available qty from product availability status (to move later and use a magento event ?)
        $productAvailabilityStatus = Mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->getForOneProduct($this->getProductId());
        if ($productAvailabilityStatus->getId())
            $qtyAvailableForSales = $productAvailabilityStatus->getpa_available_qty();

        //check that qty can be purchaseed
        if ($qtyAvailableForSales - $qty < 0) {
            switch ($this->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_NO:
                    return false;
                    break;
            }
        }
        return true;
    }


    /**
     * Product inventory status management
     *
     */
    protected function _beforeSave() {
        parent::_beforeSave();

        //if product manage stock, check inventory status
        if ($this->ManageStock()) {

            $availableQtyForSale = $this->getAvailableQtyForSale();//Optim

            //if product out of stock
            if (!$this->getIsInStock()) {
                //put product in stock if restore enabled and qty increased
                if (mage::getStoreConfig('advancedstock/general/restore_isinstock')) {
                    //set product InStock if min qty lower than getAvailableQtyForSale
                    //restore isinstock if there is backorder in all case
                    if (($this->getMinQty() < $availableQtyForSale) || ($this->getBackorders())){
                        $this->setIsInStock(true);
                    }
                }
            }
            else { //product is in stock

                //Product can go Out of Stock only if there is No Backorder
                if(!$this->getBackorders()){
                    if (($this->getMinQty() >= $availableQtyForSale) || ($availableQtyForSale == 0) ){
                        $this->setIsInStock(false);
                    }
                }
            }
        }
    }


   

    /**
     * when saving, update supply needs for product
     *
     */
    protected function _afterSave() {

        $callParentForIndexation = true;

        //BEFORE MAGENTO 1.9.X.X
        //the idea is to totally cut reindex call when ERP updates some specifics data relative to ERP internal usage
        //into the magento table "cataloginventory_stock_item" because it is absolutly useless that magento takes in consideration these change for reindexing
        //This must prevent the deadlocks
        if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
            $callParentForIndexation = $this->hasDataChanges();
            if ($callParentForIndexation) {
                $erpFieldThatDoesNotRequireMagentoAutoReIndexation = array(
                    'stock_reserved_qty',
                    'stock_ordered_qty',
                    'stock_ordered_qty_for_valid_orders',//
                    'notify_stock_qty',//warning stock level
                    'use_config_notify_stock_qty',
                    'ideal_stock_level',
                    'use_config_ideal_stock_level',
                    'erp_exclude_automatic_warning_stock_level_update',
                    'shelf_location',
                    'is_favorite_warehouse',
                );

                //if one of this field has changed, we says to magento to avoid to reindex for this time
                foreach($erpFieldThatDoesNotRequireMagentoAutoReIndexation as $noReindexRequiredField){
                    if ($this->dataHasChangedFor($noReindexRequiredField)){
                        $callParentForIndexation = false;
                        break;
                    }
                }
            }
        }

        if($callParentForIndexation){
            parent::_afterSave();
        }else{
            //To avoid any reindex (and deadlock) call parent's parent
            Mage_Core_Model_Abstract::_afterSave();
        }


        $productId = $this->getProductId();
        $stockId = $this->getstock_id();

        //check if stock changed. If so, add a stock movement to regularize
        if ($this->dataHasChangedFor('qty')) {
            //get qty from stock movement
            $stock = mage::getModel('cataloginventory/stock_item')->load($this->getId());
            $qtyFromStockMovement = $stock->getQtyFromStockMovement();
            if ($qtyFromStockMovement != $this->getqty()) {
                $diff = $this->getqty() - $qtyFromStockMovement;
                $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($stockId);
                if ($diff > 0) {
                    $sourceWarehouseId = null;
                    $targetWarehouseId = $warehouse->getId();
                } else {
                    $sourceWarehouseId = $warehouse->getId();
                    $targetWarehouseId = null;
                }
                $diff = abs($diff);
                $additionalData = array('sm_type' => 'adjustment');
                mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                        $sourceWarehouseId,
                        $targetWarehouseId,
                        $diff,
                        mage::helper('AdvancedStock')->__('Adjustment').' new Qty='.$this->getqty() ,
                        $additionalData);
                
                $debug = 'createStockMovement StackTrace for PID='.$productId.' WarehouseId='.$stockId.' QtyFromStockMovement='.$qtyFromStockMovement.' Diff='.(String)$diff;
                if(!empty($targetWarehouseId)){
                  $debug .= ' TargetWarehouseId='.$targetWarehouseId.' sourceWarehouseId=NULL ';
                }else{
                  $debug .= ' TargetWarehouseId=NULL sourceWarehouseId='.$sourceWarehouseId.' ';
                }
                try{
                  throw new Exception('LogException');
                }catch(Exception $ex){
                  $stackTrace = $ex->getTraceAsString();
                  if(!empty($stackTrace)){
                    $debug .= $stackTrace;
                  }
                }
                if(!empty($debug)){
                  mage::log($debug, null, 'erp_adjustment_stock_movement.log');
                }
                
            }
        }

        //init vars
        $qtyAfterSave = $this->getqty();
        $qtyBeforeSave = $this->getOrigData('qty');
        $orderedQty = $this->getstock_ordered_qty();
        $reservedQty = $this->getstock_reserved_qty();
        $productId = $this->getproduct_id();

        //if qty has increased
        if ($qtyAfterSave > $qtyBeforeSave) {
            //if all product are not reserved
            if ($reservedQty < $orderedQty) {
                //launch products reservation (via background tasks)
                mage::helper('BackgroundTask')->AddTask('Reserve product #' . $productId . ' for pending orders (qty has changed)',
                        'AdvancedStock/Product_Reservation',
                        'reserveProductForPendingOrders',
                        $productId
                );
            } else {
                //todo: error, reserved qty CAN NOT BE HIGHER THAN ordered qty
            }
        }

        //if qty has decreased
        if ($qtyAfterSave < $qtyBeforeSave) {
            if ($reservedQty > $qtyAfterSave) {
                //plan background task to release products that cant be reserved anymore
                mage::helper('BackgroundTask')->AddTask('Fix reservation issue after bulk stock change',
                    'AdvancedStock/Product_Reservation',
                    'FixOverReservation',
                    $this->getId()
                );
            }
        }


        if ($this->dataHasChangedFor('manage_stock')) {
            if ($this->getData('manage_stock') == 1) {
                //launch products reservation fro pending orders using background tasks
                mage::helper('BackgroundTask')->AddTask(
                    'Reserve product #' . $productId . ' for pending orders (manage stock enabled)',
                    'AdvancedStock/Product_Reservation',
                    'reserveProductForPendingOrders',
                    $productId,
                    null,
                    true,
                    1
                );
            }else{
                mage::helper('BackgroundTask')->AddTask('Release reservations when manage stock disabled',
                    'AdvancedStock/Product_Reservation',
                    'releaseAllReservation',
                    $this->getId(),
                    null,
                    true,
                    1
                );
            }
        }

        //synchronize is_in_stock value
        if ($this->dataHasChangedFor('is_in_stock')) {
            $stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
            foreach ($stocks as $stock) {
                if ($stock->getId() != $this->getId()) {
                    if ($stock->getis_in_stock() != $this->getis_in_stock()){
                        if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
                          $stock->setProcessIndexEvents(false);
                        }
                        $stock->setis_in_stock($this->getis_in_stock())->save();
                        //TODO OPTIMIZE, recurrent call
                    }
                }
            }

            //schedule product reindex
            if (!Mage::getStoreConfig('advancedstock/general/disable_product_reindex')) {
                mage::helper('BackgroundTask')->AddTask('Force product reindex for #' . $productId, 'AdvancedStock/Product_Base', 'reindex', $productId, null, true
                );
            }
        }

        //raise event
        Mage::dispatchEvent('advancedstock_stock_aftersave', array('stock' => $this));
    }

    //**********************************************************************************************************
    //**********************************************************************************************************
    //  QUANTITIES
    //**********************************************************************************************************
    //**********************************************************************************************************

    /**
     * return prefered stock level (warning stock level)
     * 
     * @return unknown
     */
    public function getWarningStockLevel() {
        $inventoryGroupName = mage::helper('AdvancedStock/MagentoVersionCompatibility')->getStockOptionsGroupName();
        $value = $this->getnotify_stock_qty();
        if ($this->getuse_config_notify_stock_qty() == 1)
            $value = Mage::getStoreConfig('cataloginventory/' . $inventoryGroupName . '/notify_stock_qty');
        if ($value == '')
            $value = 0;
        return (int) $value;
    }

    /**
     * return ideal stock level
     * @return int
     */
    public function getIdealStockLevel() {
        $value = $this->getideal_stock_level();
        if ($this->getuse_config_ideal_stock_level() == 1)
            $value = Mage::getStoreConfig('advancedstock/prefered_stock_level/ideal_stock_default_value');
        if ($value == '')
            $value = 0;

        return (int) $value;
    }

    /**
     * Return available qty
     *
     * @return unknown
     */
    public function getAvailableQty() {
        $value = $this->getqty() - $this->getstock_ordered_qty();
        if ($value < 0)
            $value = 0;
        return $value;
    }

    /**
     * Return available qty for sale for all stocks
     */
    public function getAvailableQtyForSale() {
        $productId = $this->getProductId();
        $websiteId = 0;
        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales, $productId);
        $stockLevel = 0;
        $pendingOrderQty = 0;
        foreach ($stocks as $stock) {
            if ($stock->getId() != $this->getId()) {
                $stockLevel += $stock->getQty();
                $pendingOrderQty += $stock->getstock_ordered_qty();
            } else {
                $stockLevel += $this->getQty();
                $pendingOrderQty += $this->getstock_ordered_qty();
            }
        }

        $value = $stockLevel - $pendingOrderQty;
        if ($value < 0)
            $value = 0;

        return $value;
    }

    /**
     * Return needed qty (qty to "purchase" to process pending orders)
     *
     */
    public function getNeededQty() {
        $neededQtyForIdealStockLevel = 0;
        if ($this->getAvailableQty() <= $this->getWarningStockLevel()) {
            $neededQtyForIdealStockLevel = $this->getIdealStockLevel();
        }
        $neededQty = ($this->getstock_ordered_qty() + $neededQtyForIdealStockLevel) - $this->getqty();
        return max($neededQty,0);
    }

    /**
     * Return needed qty (qty to "purchase" to process pending orders)
     * Consider only valid orders
     *
     */
    public function getNeededQtyForValidOrders() {
        $neededQtyForValidOrders = ($this->getstock_ordered_qty_for_valid_orders()) - $this->getqty();
        return max($neededQtyForValidOrders,0);
    }

    /**
     * Update qty from stock movement
     *
     */
    public function storeQty() {
        $stockHasBeenSaved = 0;
        $qty = $this->getQtyFromStockMovement();
        if ($this->getQty() != $qty){
            if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
              $this->setProcessIndexEvents(false);
            }
            $this->setqty($qty)->save();
            $stockHasBeenSaved = 1;
        }
        return $stockHasBeenSaved;
    }

    //*********************************************************************************************************************************
    //*********************************************************************************************************************************
    // Stock, Stock movements
    //*********************************************************************************************************************************
    //*********************************************************************************************************************************

    /**
     * Return an existing stock item of create One
     * 
     * @param type $productId
     * @param type $warehouseId
     */
    public function getOrCreateStock($productId, $warehouseId) {

        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);

        if($stockItem){
            return $stockItem;
        }else{
            return $this->createStock($productId, $warehouseId);
        }

    }

    /**
     * Create stock for product and warehouse
     *
     * @param unknown_type $productId
     * @param unknown_type $warehouseId
     */
    public function createStock($productId, $warehouseId) {
        //always create stock from default one
        $defaultStock = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $this->_defaultWarehouseId);
        $newStock = null;

        try {
            $newStock = mage::getModel('cataloginventory/stock_item');
            if ($defaultStock) {
                $newStock->setData($defaultStock->getData());
            }else{
                $newStock->setproduct_id($productId);
                $newStock->setmanage_stock(1);
            }
            $newStock->setQty(0);
            $newStock->setId(null);
            $newStock->setitem_id(null);
            $newStock->setstock_ordered_qty(0);
            $newStock->setstock_ordered_qty_for_valid_orders(0);
            $newStock->setnotify_stock_qty(1);
            $newStock->setuse_config_notify_stock_qty(1);
            $newStock->setideal_stock_level(1);
            $newStock->setuse_config_ideal_stock_level(1);
            $newStock->setstock_reserved_qty(0);
            $newStock->setstock_id($warehouseId);
            $newStock->setshelf_location(null);
            $newStock->save();
        } catch (Exception $ex) {
            throw new Exception('Unable to create new stock : ' . $ex->getMessage());
        }

        return $newStock;
    }

    /**
     * Return boolean to specify if manage stock
     *
     */
    public function ManageStock() {
        $manageStock = true;

        if ($this->getuse_config_manage_stock() == 1) {
            $inventoryGroupName = mage::helper('AdvancedStock/MagentoVersionCompatibility')->getStockOptionsGroupName();
            $manageStock = (Mage::getStoreConfig('cataloginventory/' . $inventoryGroupName . '/manage_stock') == 1);
        } else {
            $manageStock = ($this->getmanage_stock() == 1);
        }

        return $manageStock;
    }

    /**
     * Return stock level at a specific date (today if date is null)
     *
     * @param unknown_type $maxDate
     * @return unknown
     */
    public function getQtyFromStockMovement($maxDate = null)
    {
        $productId = $this->getproduct_id();
        $warehouseId = $this->getstock_id();

        $defaultValue = 0;

        $resourceModel = mage::getResourceModel('cataloginventory/stock_item_collection');
        $resourceModel->getSelect()->reset();

        $sql = $resourceModel
            ->getSelect()
            ->from(array('tbl_stock_movement' => $resourceModel->getTable('AdvancedStock/StockMovement')),
                array('qty' => 'sum(if(tbl_stock_movement.sm_source_stock = ' . $warehouseId . ', -tbl_stock_movement.sm_qty, tbl_stock_movement.sm_qty))'))
            ->where('(tbl_stock_movement.sm_source_stock = ' . $warehouseId . ' OR tbl_stock_movement.sm_target_stock = ' . $warehouseId . ') AND (sm_source_stock <> sm_target_stock)')
            ->where('tbl_stock_movement.sm_product_id = ' . $productId);

        //add date limit condition
        if ($maxDate != null) {
            $sql = $resourceModel 
                ->getSelect()
                ->where("sm_date <= '" . $maxDate . "'");
        }

        $value = $resourceModel->getConnection()->fetchOne($sql);

        //Fix missing stock movement after product creation
        if ($value == null && $maxDate == null) {
            mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                null,
                $warehouseId,
                $defaultValue,
                mage::helper('AdvancedStock')->__('Init product'),
                array('sm_type' => 'adjustment'));
        }

        if ($value == '' || $value == null) {
            $value = $defaultValue;
        }
        return $value;
    }

    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0)
    {
        $websiteId = 0;
        $qtyAvailableForSales = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($this->getproduct_id(),$websiteId);

        

        $result = new Varien_Object();
        $result->setHasError(false);

        if (!is_numeric($qty)) {
            $qty = Mage::app()->getLocale()->getNumber($qty);
        }


        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($this->getIsQtyDecimal());

        if (!$this->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);

            /**
              * Adding stock data to quote item
              */
            $result->setItemQty($qty);

            if (!is_numeric($qty)) {
                $qty = Mage::app()->getLocale()->getNumber($qty);
            }
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($this->getMinSaleQty() && $qty < $this->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    Mage::helper('cataloginventory')->__('The minimum quantity allowed for purchase is %s.', $this->getMinSaleQty() * 1)
                )
                ->setErrorCode('qty_min')
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($this->getMaxSaleQty() && $qty > $this->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    Mage::helper('cataloginventory')->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1)
                )
                ->setErrorCode('qty_max')
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$this->getManageStock()) {
            return $result;
        }

        if (!$this->getIsInStock()) {
            $result->setHasError(true)
                ->setMessage(Mage::helper('cataloginventory')->__('This product is currently out of stock.'))
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products are currently out of stock.'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($summaryQty) || !$this->checkQty($qty)) {
            $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName());
            $result->setHasError(true)
                ->setMessage($message)
                ->setQuoteMessage($message)
                ->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if (($qtyAvailableForSales - $summaryQty) < 0) {
                if ($this->getProductName()) {
                    if ($this->getIsChildItem()) {
                        $backorderQty = ($qtyAvailableForSales > 0) ? ($summaryQty - $qtyAvailableForSales) * 1 : $qty * 1;
                        if ($backorderQty > $qty) {
                            $backorderQty = $qty;
                        }

                        $result->setItemBackorders($backorderQty);
                    } else {
                        $orderedItems = $this->getOrderedItems();
                        $itemsLeft = ($qtyAvailableForSales > $orderedItems) ? ($qtyAvailableForSales - $orderedItems) * 1 : 0;
                        $backorderQty = ($itemsLeft > 0) ? ($qty - $itemsLeft) * 1 : $qty * 1;

                        if ($backorderQty > 0) {
                            $result->setItemBackorders($backorderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }

                    if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$this->getIsChildItem()) {
                            $result->setMessage(
                                Mage::helper('cataloginventory')->__('This product is not available in the requested quantity. %s of the items will be backordered.', ($backorderQty * 1))
                            );
                        } else {
                            $result->setMessage(
                                Mage::helper('cataloginventory')->__('"%s" is not available in the requested quantity. %s of the items will be backordered.', $this->getProductName(), ($backorderQty * 1))
                            );
                        }
                    } elseif (Mage::app()->getStore()->isAdmin()) {
                        $result->setMessage(
                            Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName())
                        );
                    }
                }
            } else {
                if (!$this->getIsChildItem()) {
                    $this->setOrderedItems($qty + (int)$this->getOrderedItems());
                }
            }
        }

        return $result;
    }

}