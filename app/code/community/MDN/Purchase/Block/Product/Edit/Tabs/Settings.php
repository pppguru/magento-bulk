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
class MDN_Purchase_Block_Product_Edit_Tabs_Settings extends Mage_Adminhtml_Block_Widget_Form {

    private $_maxDelay = 90;
    private $_product = null;

    public function setProduct($Product) {
        $this->_product = $Product;
        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }

    /**
     *
     * Exclude from supply need
     * 
     * @param unknown_type $name
     * @param unknown_type $value
     * @return unknown
     */

     public function getExcludeFromSupplyDelayCombo($name, $defaultValue) {
        $values = array('0' => $this->__('No'), '1' => $this->__('Yes'));
        $html = '<select name="' . $name . '" id="' . $name . '">';
        foreach ($values as $key => $label) {
            $selected = '';
            if ($key == $defaultValue)
                $selected = ' selected ';
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     *
     *
     * @param unknown_type $name
     * @param unknown_type $value
     * @return unknown
     */
    public function getSupplyDelayCombo($name, $value) {
        $html = '<select  id="' . $name . '" name="' . $name . '">';
        $html .= '<option value="" ></option>';
        for ($i = 1; $i <= $this->_maxDelay; $i++) {
            $selected = '';
            if ($i == $value)
                $selected = ' selected ';
            $html .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * 
     *
     */
    public function getDefaultPurchaseTaxRateCombo($name) {
        $html = '<select  id="' . $name . '" name="' . $name . '">';
        $value = $this->getProduct()->getpurchase_tax_rate();

        $html .= '<option value="">' . $this->__('[Default]') . '</option>';

        //add tax rates
        $collection = mage::getModel('Purchase/TaxRates')->getCollection();
        foreach ($collection as $item) {
            $selected = '';
            if ($item->getId() == $value)
                $selected = ' selected ';
            $html .= '<option value="' . $item->getId() . '" ' . $selected . '>' . $item->getptr_name() . '</option>';
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * Return url to refresh supply need for this product
     *
     */
    public function getRefreshSupplyNeedUrl() {
        return $this->getUrl('adminhtml/Purchase_SupplyNeeds/RefreshProduct', array('product_id' => $this->getProduct()->getId()));
    }

}