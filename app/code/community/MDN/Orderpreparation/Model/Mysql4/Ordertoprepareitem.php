<?php
 
class MDN_Orderpreparation_Model_Mysql4_ordertoprepareitem extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Orderpreparation/ordertoprepareitem', 'id');
    }
}
?>