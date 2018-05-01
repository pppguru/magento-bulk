<?php

class MDN_AdvancedStock_Model_Inventory_Product extends Mage_Core_Model_Abstract {

    /**
     * Constructor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/Inventory_Product');
    }
}