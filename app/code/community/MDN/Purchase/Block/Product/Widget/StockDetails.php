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
class MDN_Purchase_Block_Product_Widget_StockDetails extends Mage_Adminhtml_Block_Template {

    /**
     * Product get/set
     *
     * @var unknown_type
     */
    private $_product = null;

    public function setProduct($Product) {
        $this->_product = $Product;
        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }

    public function getProductSheetUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $this->getProduct()->getId()));
    }

}