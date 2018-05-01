<?php

class MDN_SmartReport_Model_System_Config_Source_GroupByDate extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {

            $this->_options = array();

                $this->_options[] = array('value' => '%d %b %y %h %p', 'label' => Mage::helper('SmartReport')->__('hour'));
                $this->_options[] = array('value' => '%d %b %y', 'label' => Mage::helper('SmartReport')->__('day'));
                $this->_options[] = array('value' => '%v %Y', 'label' => Mage::helper('SmartReport')->__('week'));
                $this->_options[] = array('value' => '%b %Y', 'label' => Mage::helper('SmartReport')->__('month'));
                $this->_options[] = array('value' => '%Y', 'label' => Mage::helper('SmartReport')->__('Year'));

        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}