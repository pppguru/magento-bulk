<?php
 
class MDN_Organizer_Model_Mysql4_TaskOrigin_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Organizer/TaskOrigin');
    }
    

}