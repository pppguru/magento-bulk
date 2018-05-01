<?php

class MDN_CompetitorPrice_Model_System_Config_Source_Frequency extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    protected $_options;

    public function toOptionArray() {

        if (!$this->_options) {
            $this->getAllOptions();
        }
        return $this->_options;
    }

    //todo : use webservice instead
    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array();

            //$this->_options[] = array( 'value' => 'hourly', 'label' => 'Hourly');
            $this->_options[] = array( 'value' => 'daily', 'label' => Mage::helper('CompetitorPrice')->__('Daily'));
            $this->_options[] = array( 'value' => 'weekly', 'label' => Mage::helper('CompetitorPrice')->__('Weekly'));
            $this->_options[] = array( 'value' => 'monthly', 'label' => Mage::helper('CompetitorPrice')->__('Monthly'));

        }
        return $this->_options;
    }


}