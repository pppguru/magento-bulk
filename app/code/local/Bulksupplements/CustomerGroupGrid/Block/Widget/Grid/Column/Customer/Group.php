<?php

class Bulksupplements_CustomerGroupGrid_Block_Widget_Grid_Column_Customer_Group extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {

    protected $_options = false;

    protected function _getOptions(){

        if(!$this->_options) {
            $methods = array();
            $methods[] = array(
                'value' =>  '',
                'label' =>  ''
            );
            $methods[] = array(
                'value' =>  '0',
                'label' =>  'Guest'
            );

            $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionArray();

            $this->_options = array_merge($methods, $groups);
        }
        return $this->_options;
    }
}
