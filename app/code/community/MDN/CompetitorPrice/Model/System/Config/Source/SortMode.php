<?php

class MDN_CompetitorPrice_Model_System_Config_Source_SortMode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

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
            $this->_options[] = array( 'value' => 'total', 'label' => Mage::helper('CompetitorPrice')->__('Sort per cheapest offer (price and shipping)'));
            $this->_options[] = array( 'value' => 'rank', 'label' => Mage::helper('CompetitorPrice')->__('Sort per google rank'));

        }
        return $this->_options;
    }


}