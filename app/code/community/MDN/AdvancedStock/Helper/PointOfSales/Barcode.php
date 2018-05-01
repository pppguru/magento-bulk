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
class MDN_AdvancedStock_Helper_PointOfSales_Barcode extends MDN_PointOfSales_Helper_Barcode
{
    public function getBarcodeForProduct($product)
    {
        return mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product);

    }

    public function getProductFromBarcode($barcode)
    {
        $product = mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode($barcode);
        return $product;
    }
}
