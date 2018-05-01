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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_Configurable extends Mage_Core_Helper_Abstract
{
    /**
     * Return configurable attributes values
     *
     * @param unknown_type $productId
     *
     * @param null         $configurableProductId
     *
     * @return unknown
     */
    public function getDescription($productId, $configurableProductId = null)
    {
        $description = '';

        //manage exceptions
        $product = mage::getModel('catalog/product')->load($productId);
        if ((!$product->getId()) || ($product->gettype_id() != 'simple'))
            return $description;

        //find configurable product
        if ($configurableProductId == null)
            $configurableProduct = $this->getConfigurableProduct($product);
        else
            $configurableProduct = mage::getModel('catalog/product')->load($configurableProductId);
        if (!$configurableProduct)
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
     *
     * @return \Mage_Core_Model_Abstract|null
     */
    protected function getConfigurableProduct($product)
    {
        $parentIdArray = mage::helper('ProductReturn/MagentoVersionCompatibility')->getProductParentIds($product);
        foreach ($parentIdArray as $parentId) {
            $parent = mage::getModel('catalog/product')->load($parentId);
            if ($parent->gettype_id() == 'configurable')
                return $parent;
        }

        return null;
    }

    /**
     * Return configurable product from simple product
     *
     * @param unknown_type $product
     *
     * @return \Mage_Core_Model_Abstract|\unknown_type
     */
    public function getPublicConfigurableProduct($product)
    {
        $parentIdArray = mage::helper('ProductReturn/MagentoVersionCompatibility')->getProductParentIds($product);
        foreach ($parentIdArray as $parentId) {
            $parent = mage::getModel('catalog/product')->load($parentId);
            if ($parent->gettype_id() == 'configurable' || $parent->gettype_id() == 'bundle' || $parent->gettype_id() == 'grouped')
                return $parent;
        }

        return $product;
    }

}