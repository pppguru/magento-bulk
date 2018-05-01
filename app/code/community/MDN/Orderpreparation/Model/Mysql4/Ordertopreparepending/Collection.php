<?php
 
/**
 * Collection
 *
 */
class MDN_Orderpreparation_Model_Mysql4_ordertopreparepending_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Orderpreparation/ordertopreparepending');
    }
}