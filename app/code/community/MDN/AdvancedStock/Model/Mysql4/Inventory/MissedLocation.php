<?php

class MDN_AdvancedStock_Model_Mysql4_Inventory_MissedLocation extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('AdvancedStock/Inventory_MissedLocation', 'eisp_shelf_location');
    }

}

?>