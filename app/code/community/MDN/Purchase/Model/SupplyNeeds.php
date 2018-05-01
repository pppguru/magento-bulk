<?php

class MDN_Purchase_Model_SupplyNeeds extends Mage_Core_Model_Abstract {
    
    //statuses
    const _StatusValidOrders = '1_valid_orders';
    const _StatusOrders = '2_orders';
    const _StatusPreferedStocklevel = '3_prefered_stock_level';
    const _StatusManual = '4_manual_supply_need';
    const _StatusPendingDelivery = '5_pending_delivery';

    public function _construct() {
        parent::_construct();
        $this->_init('Purchase/SupplyNeeds');
    }

    /**
     * Statuses for supply needs
     * @return <type>
     */
    public function getStatuses() {
        $statuses = array();

        $statuses[self::_StatusValidOrders] = mage::helper('AdvancedStock')->__(self::_StatusValidOrders);
        $statuses[self::_StatusOrders] = mage::helper('AdvancedStock')->__(self::_StatusOrders);
        $statuses[self::_StatusPreferedStocklevel] = mage::helper('AdvancedStock')->__(self::_StatusPreferedStocklevel);
        $statuses[self::_StatusManual] = mage::helper('AdvancedStock')->__(self::_StatusManual);
        $statuses[self::_StatusPendingDelivery] = mage::helper('AdvancedStock')->__(self::_StatusPendingDelivery);

        return $statuses;
    }

    /**
     * Load supply needs data only for one specific warehouse (uses
     * @param <type> $warehouseId
     */
    public function restrictToWarehouse($warehouseId)
    {
        
    }
    
}