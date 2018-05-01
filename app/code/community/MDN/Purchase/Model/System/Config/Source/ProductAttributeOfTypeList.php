<?php

class MDN_Purchase_Model_System_Config_Source_ProductAttributeOfTypeList extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {
            $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                            ->setEntityTypeFilter($entityTypeId)
                            ->addFieldToFilter('frontend_input', 'select');
            
            //add empty
            $options[] = array(
                'value' => '',
                'label' => '',
            );

            foreach ($attributes as $attribute) {
                $options[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getName(),
                );
            }

            $this->_options = $options;
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}