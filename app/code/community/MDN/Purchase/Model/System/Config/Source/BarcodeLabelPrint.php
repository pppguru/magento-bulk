<?php

class MDN_Purchase_Model_System_Config_Source_BarcodeLabelPrint extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    protected $_options;
    
    const kAllProducts = 'all_products';
    const kDeliveredProducts = 'delivered_products';

    public function toOptionArray($isMultiselect = false) {
        if (!$this->_options) {
            $this->getAllOptions();
        }
        return $this->_options;
    }

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => self::kAllProducts,
                    'label' => Mage::helper('purchase')->__('All products in PO'),
                ),
                array(
                    'value' => self::kDeliveredProducts,
                    'label' => Mage::helper('purchase')->__('Delivered products only'),
                )
            );
        }
        return $this->_options;
    }

}