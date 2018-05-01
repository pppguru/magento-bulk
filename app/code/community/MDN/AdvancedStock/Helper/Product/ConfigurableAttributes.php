<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Helper_Product_ConfigurableAttributes extends Mage_Core_Helper_Abstract {

    /**
     * Return configurable attributes values
     *
     * @param unknown_type $productId
     * @return unknown
     */
    public function getDescription($productId) {
        $description = '';

        //manage exceptions
        $product = mage::getModel('catalog/product')->load($productId);
        if ((!$product->getId()) || ($product->gettype_id() != 'simple'))
            return $description;

        //find configurable product
        $configurableProduct = $this->getConfigurableProduct($product);
        if (!$configurableProduct)
            return $description;
        if ($configurableProduct->gettype_id() != 'configurable')
            return $description;

        //build attributes string
        $attributes = $configurableProduct->getTypeInstance()->getConfigurableAttributesAsArray($configurableProduct);
        foreach ($attributes as $att) {
            $description .= $att['label'] . ': ' . $product->getAttributeText($att['attribute_code']) . ', ';
        }
        if (strlen($description) > 2)
            $description = substr($description, 0, strlen($description) - 2);

        if ($description != '')
            $description = ' (' . $description . ') ';

        return $description;
    }

    /**
     * Return configurable product from simple product
     *
     * @param unknown_type $product
     */
    public function getConfigurableProduct($product) {
        $parentIdArray = mage::helper('AdvancedStock/MagentoVersionCompatibility')->getProductParentIds($product);
        foreach ($parentIdArray as $parentId) {
            $parent = mage::getModel('catalog/product')->load($parentId);
            return $parent;
        }

        return null;
    }

    public function addConfigurableAttributesColumn($grid, $getter = 'entity_id') {
        if (mage::getStoreConfig('advancedstock/miscellaneous/display_configurable_attributes') != 1)
            return false;

        $grid->addColumn('configurable_attributes', array(
            'header' => Mage::helper('AdvancedStock')->__('Attributes'),
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_ConfigurableAttributes',
            'getter' => $getter
        ));
    }

}