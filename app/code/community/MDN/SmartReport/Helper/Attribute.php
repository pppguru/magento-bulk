<?php


class MDN_SmartReport_Helper_Attribute extends Mage_Core_Helper_Abstract {

    protected $_cache = array();

    public function getAttributeCode($attributeId)
    {
        $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
        return $attribute->getAttributeCode();
    }

    /**
     * @param $attributeCode
     * @param bool $addAll
     * @return array
     */
    public function getAttributeValues($attributeCode, $addAll = true)
    {
        if (!isset($this->_cache[$attributeCode]))
        {
            $product = Mage::getModel('catalog/product');
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addFieldToFilter('main_table.attribute_code', $attributeCode)
                ->load(false);
            $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
            $manufacturers = $attribute->getSource()->getAllOptions(false);
            $this->_cache[$attributeCode] = array();

            if ($addAll)
                $this->_cache[$attributeCode][] = array('value' => '*', 'label' => 'ALL');

            foreach ($manufacturers as $manufacturer) {
                if ($manufacturer['value'])
                    $this->_cache[$attributeCode][] = array('value' => $manufacturer['value'], 'label' => $manufacturer['label']);
            }
        }

        return $this->_cache[$attributeCode];
    }

    public function getAttributeValueLabel($attributeCode, $value)
    {
        foreach($this->getAttributeValues($attributeCode) as $item)
        {
            if ($item['value'] == $value)
                return $item['label'];
        }
    }

    public function getAllAttributes()
    {
        return Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->addFieldToFilter('frontend_input', array('in' => array('select', 'price', 'date', 'datetime', 'multiselect', 'boolean', 'weight', 'text')));
    }

    public function getFrontEndInput($attributeCode)
    {
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attributeCode);
        return $attribute->getfrontend_input();
    }

    public function getIdFromLabel($attributeCode, $attributeLabel)
    {
        foreach($this->getAttributeValues($attributeCode) as $item)
        {
            if ($item['label'] == $attributeLabel)
                return $item['value'];
        }
    }
}