<?php
 
class MDN_ExtensionConflict_Model_Mysql4_ExtensionConflict_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ExtensionConflict/ExtensionConflict');
    }
    
  
}