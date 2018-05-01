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
class MDN_Purchase_Block_Supplier_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    private $_supplier = null;

    /**
     * Constructeur: on charge le devis
     *
     */
    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'supplier';
        $this->_blockGroup = 'Purchase';

        parent::__construct();

        $this->_updateButton('save', 'onclick', 'beforeSaveSupplier()');
        if (!Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_supplier/edit'))
        {
            $this->_removeButton('save');
        }

        //Load supplier
        $sup_id = Mage::app()->getRequest()->getParam('sup_id', false);
        $model = Mage::getModel('Purchase/Supplier');
        $this->_supplier = $model->load($sup_id);

        //add delete button
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_supplier/delete'))
        {
            $url = $this->getUrl('*/*/delete', array('sup_id' => $sup_id));
            $this->_addButton(
                    'delete', array(
                'label' => Mage::helper('purchase')->__('Delete'),
                'onclick' => "if (confirm('".$this->__('Are you sure ?')."')) { setLocation('" . $url . "')}",
                'level' => -1,
                'class' => 'delete'
                    )
            );
        }
        
        //add new order button
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_supplier/new_order'))
        {
            $url = $this->getUrl('adminhtml/Purchase_Orders/create', array('supplier' => $sup_id));
            $this->_addButton(
                    'new_order', array(
                'label' => Mage::helper('purchase')->__('New order'),
                'onclick' => "window.location.href='" . $url . "'",
                'level' => -1,
                'class' => 'add'
                    )
            );
        }
    }

    public function getHeaderText() {
        return $this->getSupplier()->getsup_name();
    }

    /**
     * Retourne l'url pour retourner a la liste des manufacturers
     */
    public function GetBackUrl() {
        return $this->getUrl('adminhtml/Purchase_Suppliers/List', array());
    }

    /**
     * Retourne un combo avec la liste des pays
     *
     * @param unknown_type $name
     * @return unknown
     */
    public function getCountryCombo($name, $value) {
        $retour = '<select  id="' . $name . '" name="' . $name . '">';
        $retour .= '<option value=""></option>';

        //charge la liste des pays
        $collection = Mage::getModel('directory/country')
                ->getResourceCollection()
                ->loadByStore();
        foreach ($collection as $item) {
            if ($item->getcountry_id() == $value)
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item->getcountry_id() . '" ' . $selected . '>' . $item->getName() . '</option>';
        }

        $retour .= '</select>';
        return $retour;
    }

    /**
     * Retourne l'objet manufacturer
     *
     * @return unknown
     */
    public function getSupplier() {
        return $this->_supplier;
    }

    public function getSaveUrl() {
        return $this->getUrl('adminhtml/Purchase_Suppliers/Save');
    }

}
