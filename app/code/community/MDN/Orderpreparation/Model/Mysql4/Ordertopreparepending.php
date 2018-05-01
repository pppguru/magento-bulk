<?php
 
class MDN_Orderpreparation_Model_Mysql4_ordertopreparepending extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Orderpreparation/ordertopreparepending', 'opp_num');
    }
    
    /**
     * Vide la table
     *
     */
    public function TruncateTable()
    {
    	$this->_getWriteAdapter()->delete($this->getMainTable(), "1=1");
    }
}
?>