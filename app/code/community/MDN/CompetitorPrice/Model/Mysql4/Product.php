<?php

class MDN_CompetitorPrice_Model_Mysql4_Product extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('CompetitorPrice/Product', 'id');
    }

    public function TruncateTable()
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), "1=1");
    }

}