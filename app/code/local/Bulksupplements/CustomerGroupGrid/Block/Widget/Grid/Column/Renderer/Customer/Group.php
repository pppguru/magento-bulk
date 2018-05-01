<?php

class Bulksupplements_CustomerGroupGrid_Block_Widget_Grid_Column_Renderer_Customer_Group extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected $_options = false;

    protected function _getOptions(){

        if(!$this->_options) {
            $methods = array();
            $methods[0] = 'Guest';

            $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash();
            $this->_options = array_merge($methods,$groups);
        }
        return $this->_options;
    }

    public function render(Varien_Object $row){
        $value = $this->_getValue($row);
        $options = $this->_getOptions();
        return isset($options[$value]) ? $options[$value] : $value;
    }
}
