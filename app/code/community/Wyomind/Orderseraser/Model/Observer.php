<?php

class Wyomind_Orderseraser_Model_Observer {

    public function addActionColumn(Varien_Event_Observer $observer) {

        $block = $observer->getEvent()->getBlock();
        $this->_block = $block;

        if (get_class($block) == Mage::getStoreConfig("orderseraser/system/grid")) {
          
            $actions = array();

            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/delete')) {
                $actions[] = array(
                    'caption' => Mage::helper('sales')->__('Delete'),
                    'url' => array('base' => 'orderseraser/adminhtml_orderseraser/delete'),
                    'confirm' => Mage::helper('sales')->__('Are your sure your want to delete this order and to erase all linked data ? '),
                    'field' => 'order_id'
                );



                $block->addColumnAfter('Delete', array(
                    'header' => Mage::helper('sales')->__('Delete'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => $actions,
                    'filter' => false,
                    'sortable' => false,
                    'is_system' => true,
                        ), 'status');
            }
        }

        return $observer;
    }

    public function addMassAction($observer) {

        $block = $observer->getEvent()->getBlock();
        $this->_block = $block;
        if (get_class($block) == 'Mage_Adminhtml_Block_Widget_Grid_Massaction'
                && $block->getRequest()->getControllerName() == 'sales_order') {
           

            $block->addItem('delete_order', array(
                'label' => Mage::helper('sales')->__('Delete'),
                'url' => $block->getUrl('orderseraser/adminhtml_orderseraser/massdelete'),
            ));
        }
    }

}

