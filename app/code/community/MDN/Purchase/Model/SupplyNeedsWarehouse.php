<?php

class MDN_Purchase_Model_SupplyNeedsWarehouse extends Mage_Core_Model_Abstract {
    
    public function _construct() {
        parent::_construct();
        $this->_init('Purchase/SupplyNeedsWarehouse');
    }
    
}