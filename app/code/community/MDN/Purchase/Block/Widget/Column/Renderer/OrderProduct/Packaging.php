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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Packaging extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $item) {
        $productId = $item->getpop_product_id();
        $value = $item->getpop_packaging_id();
        $html = '';

        //add packaging values
        foreach (mage::helper('purchase/Product_Packaging')->getPackagingForProduct($productId) as $packaging) {
            $html .= '<input type="hidden" id="packaging_' . $packaging->getId() . '" value="' . $packaging->getpp_qty() . '">';
        }

        $name = 'pop_packaging_id_' . $item->getId();
        $onChange = 'persistantProductGrid.logChange(this.name, \'' . $value . '\'); displayFinalQty(' . $item->getId() . ');';
        $onChange .= 'updateQtyFromPackageCount(' . $item->getId() . ');';
        $onChange .= 'persistantProductGrid.logChange(\'pop_qty_' . $item->getId() . '\', ' . $item->getpop_qty() . ');';
        $html .= mage::helper('purchase/Product_Packaging')->getPackagingPurchaseCombobox($item->getpop_product_id(), $name, $value, $onChange);
        $html .= '<br>' . $this->__('Total : ') . '<span id="span_final_qty_' . $item->getId() . '" class="finalQty">0</span>';

        return $html;
    }

}