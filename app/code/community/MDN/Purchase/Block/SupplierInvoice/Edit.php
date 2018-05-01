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
class MDN_Purchase_Block_SupplierInvoice_Edit extends Mage_Adminhtml_Block_Widget_Form {

    private $_po_num = null;
    private $_order = null;

    private $_supplierInvoiceId = null;

    public function setPurchaseOrderId($poId){
        $this->_po_num = $poId;
    }

    public function getPurchaseOrderId(){
        return $this->_po_num;
    }

    public function getOrder() {
        if ($this->_order == null) {
            $this->_order = Mage::getModel('Purchase/Order')->load($this->getPurchaseOrderId());
        }
        return $this->_order;
    }

    public function loadSupplierInvoice($supplierInvoiceId){
        $this->_supplierInvoiceId = $supplierInvoiceId;
        $this->setPurchaseOrderId($this->getCurrentSupplierInvoice()->getpsi_po_id());
    }

    public function getCurrentSupplierInvoiceId(){
        return $this->_supplierInvoiceId;
    }

    public function getCurrentSupplierInvoice() {
        return Mage::getModel('Purchase/PurchaseSupplierInvoice')->load($this->getCurrentSupplierInvoiceId());
    }

    public function getCurrentSupplierInvoiceData($field){
        $fieldValue = '';

        $model = $this->getCurrentSupplierInvoice();
        if($model->getId()>0){
            $field = 'get'.$field;
            $fieldValue = $model->$field();
        }
        return $fieldValue;
    }

    public function getSaveUrl() {
        $redirectArray = array();
        if($this->getPurchaseOrderId()){
            $redirectArray['po_num'] = $this->getPurchaseOrderId();
        }
        return $this->getUrl('adminhtml/Purchase_SupplierInvoice/Save',$redirectArray);
    }

    public function getStatusCombo($id,$value) {
        return mage::helper('purchase/SupplierInvoice')->getStatusLabelAsCombo($id,$value);
    }
    
   
}