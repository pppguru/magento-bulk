<?php

class MDN_AdvancedStock_Model_Mysql4_Inventory extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('AdvancedStock/Inventory', 'ei_id');
    }

}

?>