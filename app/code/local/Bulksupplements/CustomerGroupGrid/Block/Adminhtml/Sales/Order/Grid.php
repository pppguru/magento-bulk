<?php

class Bulksupplements_CustomerGroupGrid_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareColumns()
    {
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('customer_group_id', array(
            'header'    =>  Mage::helper('customer')->__('Customer Group'),
            'width'     =>  '100',
            'index'     =>  'customer_group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumnsOrder('customer_group_id', 'shipping_name');

        return $this;
    }
}
