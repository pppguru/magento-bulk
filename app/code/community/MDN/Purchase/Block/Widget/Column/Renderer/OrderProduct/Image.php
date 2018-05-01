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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Image extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row) {
        $html = '';

        $heigth = 50;
        $width = 50;
        
        $productId = $row->getpop_product_id();

        $imageUrl = Mage::helper('AdvancedStock/Product_Image')->getProductImageUrl($productId);

        if ($imageUrl) {
            $html =  '<a href="' . $this->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId)) . '" target="_blanck">';
            $html .= '<img src="' . $imageUrl . '" width="'.$width.'" height="'.$heigth.'"></a>';
        }

        return $html;
    }

}