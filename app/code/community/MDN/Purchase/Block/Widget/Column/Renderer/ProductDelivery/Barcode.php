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
class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Barcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        //get barcode
        $productId = $row->getpop_product_id();
        $barcode = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);

        $name = 'delivery_barcode_' . $row->getId();
        $html = $barcode;
        $onChange = 'onchange="persistantDeliveryGrid.logChange(this.name, \'\')"';
        $html .= '<br><input type="text" id="' . $name . '" name="' . $name . '" value="" size="13" ' . $onChange . '>';
        return $html;
    }

}