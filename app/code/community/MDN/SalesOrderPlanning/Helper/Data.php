<?php

class MDN_SalesOrderPlanning_Helper_Data extends Mage_Core_Helper_Abstract {

    public function planningIsEnabled() {
        return (Mage::getStoreConfig('planning/general/enable_planning') == 1);
    }

}