<?php
 
class MDN_Organizer_Model_Mysql4_TaskCategory extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Organizer/TaskCategory', 'otc_id');
    }
}
?>