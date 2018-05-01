<?php
 
/**
 * Collection de quotation
 *
 */
class MDN_Orderpreparation_Model_Mysql4_ordertoprepare_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Orderpreparation/ordertoprepare');
    }
    

}