<?php

class MDN_Purchase_Model_Mysql4_Order_History extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('Purchase/Order_History', 'poh_id');
    }

}
?>