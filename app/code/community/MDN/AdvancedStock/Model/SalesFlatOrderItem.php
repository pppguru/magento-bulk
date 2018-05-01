<?php

class MDN_AdvancedStock_Model_SalesFlatOrderItem extends Mage_Core_Model_Abstract {

    private $_orderItem = null;

    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/SalesFlatOrderItem');
    }

    /**
     * Return matching sales order Item
     * 
     * @return <type>
     */
    public function getOrderItem()
    {
        if ($this->_orderItem == null)
        {
            $this->_orderItem = Mage::getModel('sales/order_item')->load($this->getId());
        }
        return $this->_orderItem;
    }

    /**
     * Set matching order item
     * @param <type> $orderItem
     */
    public function setOrderItem($orderItem)
    {
        $this->_orderItem = $orderItem;
    }

    /**
     * Reset reserved qty if preparation warehouse change
     */
    protected function _beforeSave() {
        parent::_beforeSave();

        if ($this->getpreparation_warehouse() != $this->getOrigData('preparation_warehouse')) {
            $this->setreserved_qty(0);
        }
    }
    
    /**
     * After save
     */
    protected function _afterSave() {
        parent::_afterSave();

        //if preparation warehouse changed, plan operations
        if ($this->getpreparation_warehouse() != $this->getOrigData('preparation_warehouse')) {
            $this->applyWarehouseChange($this->getOrigData('preparation_warehouse'), $this->getpreparation_warehouse());
        }

        //if reserved qty change, 
        if ($this->getreserved_qty() != $this->getOrigData('reserved_qty')) {
            Mage::dispatchEvent('advancedstock_order_item_reserved_qty_changed', array('order_item' => $this->getOrderItem(), 'erp_order_item' => $this));
        }
        
        //if serials have changed
        if ($this->getserials() != $this->getOrigData('serials')) {
            Mage::getModel('AdvancedStock/ProductSerial')->updateForOrderItem($this->getOrderItem());
        }
        
    }

    /**
     * Process warehouse change
     */
    public function applyWarehouseChange($oldWarehouseId, $newWarehouseId) {

        $productId = $this->getOrderItem()->getproduct_id();

        if (!$productId)
            return false;
        if (!Mage::helper('AdvancedStock/Product_Base')->productExists($productId))
            return false;

        $debug = 'warehouse change from ' . $oldWarehouseId . ' to ' . $newWarehouseId . ', ';

        //update data (ordered & reserved qty) for old warehouse
        if ($oldWarehouseId) {
            $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $oldWarehouseId);
            if ($stockItem) {
                mage::helper('AdvancedStock/Product_Ordered')->storeOrderedQtyForStock($stockItem, $productId);
                mage::helper('AdvancedStock/Product_Reservation')->storeReservedQtyForStock($stockItem, $productId);
            }
        }

        //update data for new warehouse
        $stockItem = mage::getModel('cataloginventory/stock_item')->getOrCreateStock($productId, $newWarehouseId);
        mage::helper('AdvancedStock/Product_Ordered')->storeOrderedQtyForStock($stockItem, $productId);
        $order = mage::getModel('sales/order')->load($this->getOrderItem()->getorder_id()); //todo : find a solution to avoid loading order...
        $this->setOrigData('preparation_warehouse', $this->getpreparation_warehouse());  //important, else, loop calls...
        $orderItem = $this->getOrderItem();
        mage::helper('AdvancedStock/Product_Reservation')->reserveOrderProduct($order, $orderItem);

        //if product has parent, affect the same warehouse to the parent
        $parentItemId = $this->getOrderItem()->getparent_item_id();
        if ($parentItemId) {
            $parentItem = Mage::getModel('AdvancedStock/SalesFlatOrderItem')->load($parentItemId);
            $parentItem->setpreparation_warehouse($newWarehouseId)->save();
        }

        Mage::dispatchEvent('advancedstock_order_item_preparation_warehouse_changed', array('order_item' => $this->getOrderItem(), 'erp_order_item' => $this));
    }

    /**
     * Return preparation warehouse
     */
    public function getPreparationWarehouse() {
        if ($this->_preparationWarehouse == null) {
            $this->_preparationWarehouse = mage::getModel('AdvancedStock/Warehouse')->load($this->getpreparation_warehouse());
        }
        return $this->_preparationWarehouse;
    }



}