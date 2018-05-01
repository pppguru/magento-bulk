<?php
 
class MDN_ExtensionConflict_Model_Mysql4_ExtensionConflict extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('ExtensionConflict/ExtensionConflict', 'ec_id');
    }
    
    /**
     * truncate table
     *
     */
    public function TruncateTable()
    {
    	$this->_getWriteAdapter()->delete($this->getMainTable(), "1=1");
    }
    
}
?>