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
class MDN_AdvancedStock_Block_Transfer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     *
     *
     */
    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'Transfer';
        $this->_blockGroup = 'AdvancedStock';

        parent::__construct();

        if ($this->getTransfer()->getId()) {
            
            $saveAction = 'persistantProductGrid.storeLogInTargetInput();';
            if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/stock_transfer/add_products'))
                $saveAction .= 'persistantAddProductGrid.storeLogInTargetInput();';
            $saveAction .= 'editForm.submit()';
            $this->_updateButton('save', 'onclick', $saveAction);

            $this->_addButton(
                    'print', array(
                'label' => Mage::helper('AdvancedStock')->__('Print'),
                'onclick' => "window.location.href='" . $this->getUrl('adminhtml/AdvancedStock_Transfer/Print', array('st_id' => $this->getTransfer()->getId())) . "'",
                'level' => -1
                    )
            );

            $this->_addButton(
                    'apply', array(
                'label' => Mage::helper('AdvancedStock')->__('Apply transfer'),
                'onclick' => "if (confirm('" . $this->__('Are you sure ? this will create the stock movements for products in transfer') . "')) {window.location.href='" . $this->getUrl('adminhtml/AdvancedStock_Transfer/Apply', array('st_id' => $this->getTransfer()->getId())) . "'}",
                'level' => -1
                    )
            );
            
            $this->_addButton(
                    'add_products_with_scan', array(
                'label' => Mage::helper('AdvancedStock')->__('Add products with scanner'),
                'onclick' => "document.location.href='".Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Transfer/AddProductsWithScanner', array('st_id' => $this->getTransfer()->getId()))."'",
                'class' => 'add',
                'level' => -1
                    )
            );
            
            
        }
    }

    public function getHeaderText() {
        if ($this->getTransfer()->getId())
            return $this->__('Edit transfer (%s)', $this->getTransfer()->getst_name());
        else
            return $this->__('New transfer');
    }

    /**
     * Return url to submit form
     *
     * @return unknown
     */
    public function getSaveUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Transfer/Save');
    }

    public function getBackUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Transfer/Grid');
    }

    /**
     * return current
     *
     * @return unknown
     */
    public function getTransfer() {
        return mage::registry('current_transfer');
    }

}
