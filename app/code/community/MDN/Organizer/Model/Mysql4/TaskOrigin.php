<?php
 
class MDN_Organizer_Model_Mysql4_TaskOrigin extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Organizer/TaskOrigin', 'oto_id');
    }
}
?>