<?php

class Bulksupplements_CustomerGroupGrid_Model_Observer {
    public function addCustomerGroupColumn(Varien_Event_Observer $observer){
        if($observer->getBlock()->getType() == 'adminhtml/sales_order_grid'){
            $block = $observer->getEvent()->getBlock();
            $sales_order_grid_block = $observer->getBlock();

            $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash();

            $block->addColumnAfter('customer_group_id', array(
                'header' 	=> Mage::helper('customer')->__('Customer Group'),
                'width'		=> '100',
                'index'		=> 'customer_group_id',
                'type'		=> 'options',
                'options'	=> $groups,
            ), 'shipping_name');
            $block->sortColumnsByOrder();
        }
    }
}
