<?php

/**
 * Collection de quotation
 *
 */
class MDN_Purchase_Model_Mysql4_Order_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Purchase/Order_History');
    }

}