<?php
 
class MDN_Organizer_Model_Mysql4_Task extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Organizer/Task', 'ot_id');
    }
}
?>