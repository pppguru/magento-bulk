<?php

/**
 * Magento Fianet Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Gr
 * @package    Gr_Fianet
 * @author     Nicolas Fabre <nicolas.fabre@groupereflect.net>
 * @copyright  Copyright (c) 2008 Nicolas Fabre
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_System_Config_ProductAttribute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
        if (!$this->_options) {
            $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
            $attributes   = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($entityTypeId);

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

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}