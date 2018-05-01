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
class MDN_Purchase_Helper_SupplierInvoice extends Mage_Core_Helper_Abstract {

    public function getStatusLabel(){
        return array(
            MDN_Purchase_Model_PurchaseSupplierInvoice::STATUS_PAID => $this->__('Paid'),
            MDN_Purchase_Model_PurchaseSupplierInvoice::STATUS_PENDING => $this->__('Pending')
        );
    }

    public function getStatusLabelAsArray()
    {
        $data = array();
        $data[] = array('label' => '', 'value' => '');
        $list = $this->getStatusLabel();
        foreach ($list as $value => $label) {
            $data[] = array('label' => $label, 'value' => $value);
        }
        return $data;
    }

    public function getStatusLabelAsCombo($id,$value) {

        $labelArray = $this->getStatusLabel();
        $html = '<select id="' . $id . '" name="' . $id . '">';
        foreach ($labelArray as $statusCode => $statusLabel){
            $selected = ($statusCode == $value)?' selected ':'';
            $html .= '<option value="'.$statusCode.'" '.$selected.'>' .$statusLabel . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getStatusHtmlColor($status,$forceLabel = null){
        $label = ($forceLabel)?$forceLabel:ucfirst($this->__($status));
        $color = ($status == MDN_Purchase_Model_PurchaseSupplierInvoice::STATUS_PAID)? 'green':'red';
        return '<font color="' . $color . '">' . $label . '</font>';
    }

    

    public function getInvoiceCurrencySymbol($po)
    {
        $code = '';

        if($po->getpo_currency()) {
            $currency = mage::getModel('directory/currency')->load($po->getpo_currency());
            if ($currency->getId()) {
                $code = $currency->getCode();
            }
        }

        return $code;
    }


}