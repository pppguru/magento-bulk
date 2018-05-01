<?php

class MDN_AdvancedStock_Model_System_Config_Source_OrderStatus extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    protected $_options;

    public function toOptionArray() {

        if (!$this->_options) {
            $this->getAllOptions();
        }
        return $this->_options;
    }

    public function getAllOptions() {
        if (!$this->_options) {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();

            $this->_options = array();
            foreach ($statuses as $code => $label) {
                $this->_options[] = array(
                    'value' => $code,
                    'label' => $label
                );
            }
        }
        return $this->_options;
    }

}