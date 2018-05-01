<?php

class MDN_AdvancedStock_Model_Mysql4_SalesFlatOrderItem extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('AdvancedStock/SalesFlatOrderItem', 'esfoi_item_id');
    }

    /**
     * Insert record in table based on the order item
     *
     * @param <type> $orderItem
     */
    public function initializeRecord($orderItem) {

        //check that record doesnt exist yet
        $select = $this->_getReadAdapter()->select()
                ->reset()
                ->from($this->getMainTable(), 'COUNT(*)')
                ->where('esfoi_item_id= '.$orderItem->getId());
        $exists = $this->_getReadAdapter()->fetchOne($select);

        //if does not exist, insert record
        if (!$exists)
        {
            $this->_getWriteAdapter()->insert(
                    $this->getMainTable(),
                    array(
                        'esfoi_item_id' => $orderItem->getId()
                    )
            );
        }
        
        return $this;
    }

}