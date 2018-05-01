<?php

class MDN_AdvancedStock_Model_Mysql4_Inventory_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('AdvancedStock/Inventory_Product');
    }


}
