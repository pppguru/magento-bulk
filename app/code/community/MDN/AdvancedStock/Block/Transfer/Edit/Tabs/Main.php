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
class MDN_AdvancedStock_Block_Transfer_Edit_Tabs_Main extends Mage_Adminhtml_Block_Widget_Form {

    private $_transfer = null;

    public function __construct() {
        $this->_blockGroup = 'AdvancedStock';
        $this->_objectId = 'id';
        $this->_controller = 'Transfer';

        parent::__construct();
    }

    /**
     * return current
     *
     * @return unknown
     */
    public function getTransfer() {
        if ($this->_transfer == null) {
            $this->_transfer = mage::registry('current_transfer');
        }
        return $this->_transfer;
    }

    /**
     * Prepare form data
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {

        $transfer = $this->getTransfer();

        $form = new Varien_Data_Form();

       
        $fieldset = $form->addFieldset('transfer_fieldset', array(
                    'legend' => Mage::helper('AdvancedStock')->__('Main')
                ));

        if ($transfer->getId()) {
            $fieldset->addField('st_id', 'hidden', array(
                'name' => 'st_id',
                'label' => mage::helper('AdvancedStock')->__('Id')
            ));
        }

        if ($transfer->getId()) {
            $fieldset->addField('st_created_at', 'label', array(
                'name' => 'st_created_at',
                'label' => mage::helper('AdvancedStock')->__('Created at')
            ));
        }
        
        $fieldset->addField('st_name', 'text', array(
            'name' => 'st_name',
            'label' => mage::helper('AdvancedStock')->__('Name'),
            'required' => true,
        ));

        $fieldset->addField('st_status', 'select', array(
            'name' => 'st_status',
            'label' => mage::helper('AdvancedStock')->__('Status'),
            'options' => mage::getModel('AdvancedStock/StockTransfer')->getStatuses()
        ));

        $fieldset->addField('st_comments', 'textarea', array(
            'name' => 'st_comments',
            'label' => mage::helper('AdvancedStock')->__('Comments')
        ));

        $fieldset->addField('st_source_warehouse', 'select', array(
            'name' => 'st_source_warehouse',
            'label' => mage::helper('AdvancedStock')->__('Source warehouse'),
            'options' => $this->getWarehouses()
        ));

        $fieldset->addField('st_target_warehouse', 'select', array(
            'name' => 'st_target_warehouse',
            'label' => mage::helper('AdvancedStock')->__('Target warehouse'),
            'options' => $this->getWarehouses()
        ));

        if ($transfer->getId())
        {
            if (($transfer->getst_status() == MDN_AdvancedStock_Model_StockTransfer::STATUS_NEW)) {
                $fieldset->addField('populate_with_supply_needs', 'button', array(
                    'name' => 'populate_with_supply_needs',
                    'label' => mage::helper('AdvancedStock')->__('Populate with supply needs'),
                    'value' => mage::helper('AdvancedStock')->__('Populate with supply needs'),
                    'onclick' => "setLocation('".$this->getUrl('*/*/populateWithSupplyNeeds', array('transfer_id' => $transfer->getId()))."')",
                    'class' => 'scalable button',
                    'note'  => $this->__('Populates this transfer with products needed in "%s" and available in "%s"', $transfer->getTargetWarehouse()->getStockName(), $transfer->getSourceWarehouse()->getStockName())
                ));
            }
        }

        //hidden field to store add product information
        $fieldset->addField('add_product_log', 'hidden', array(
            'name' => 'add_product_log',
            'label' => mage::helper('AdvancedStock')->__('Add product logs')
        ));
        $fieldset->addField('product_log', 'hidden', array(
            'name' => 'product_log',
            'label' => mage::helper('AdvancedStock')->__('product logs')
        ));

        if ($transfer->getId())
            $form->addValues($transfer->getData());

        $form->setAction($this->getUrl('*/*/save'));
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return warehouses
     */
    protected function getWarehouses()
    {
        return mage::helper('AdvancedStock/Warehouse')->getWarehouses(true);
    }
}
