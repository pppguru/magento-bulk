<?php

class MDN_Purchase_Model_Order_History extends Mage_Core_Model_Abstract {

   public function _construct() {
        parent::_construct();
        $this->_init('Purchase/Order_History');
    }
}