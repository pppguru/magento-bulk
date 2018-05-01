<?php

class MDN_Orderpreparation_Model_Source_PickingListMode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    const kMerged = 'merged';
    const kOnePagePerOrder = 'one_page_per_order';
    const kBoth = 'both';

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => self::kMerged,
                    'label' => mage::helper('Orderpreparation')->__('Merged (contains products to pick for all orders)'),
                ),
                array(
                    'value' => self::kOnePagePerOrder,
                    'label' => mage::helper('Orderpreparation')->__('One page per order'),
                ),
                array(
                    'value' => self::kBoth,
                    'label' => mage::helper('Orderpreparation')->__('Both (Merged + Order by Order)'),
                )
            );
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}