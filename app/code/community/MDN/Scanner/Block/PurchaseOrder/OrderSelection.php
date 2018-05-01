<?php

class MDN_Scanner_Block_PurchaseOrder_OrderSelection extends Mage_Adminhtml_Block_Widget_Form {

    private $_orders = null;

    /**
     * Return purchase orders
     *
     * @return unknown
     */
    public function getOrders() {
        if ($this->_orders == null) {
            $supplierId = $this->getRequest()->getParam('sup_num');
            $this->_orders = mage::getModel('Purchase/Order')
                            ->getCollection()
                            ->addFieldToFilter('po_sup_num', $supplierId)
                            ->addFieldToFilter('po_status', array('neq' => 'complete'))
                            ->setOrder('po_supply_date', 'desc');
        }
        return $this->_orders;
    }

}