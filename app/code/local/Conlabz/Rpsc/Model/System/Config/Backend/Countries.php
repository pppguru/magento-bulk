<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Model_System_Config_Backend_Countries
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function beforeSave($object) 
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == Conlabz_Rpsc_Helper_Data::ALLOWED_COUNTRIES_ATTR_CODE) {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = array();
            }
            $object->setData($attributeCode, implode(',', $data));
        }
        return $this;
    }

    public function afterLoad($object) 
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == Conlabz_Rpsc_Helper_Data::ALLOWED_COUNTRIES_ATTR_CODE) {
            $data = $object->getData($attributeCode);
            if ($data && !is_array($data)) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }
        return $this;
    }
}
