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
class MDN_Purchase_Block_Supplier_Edit_Tabs_Info extends Mage_Adminhtml_Block_Widget_Form {

    private $_supplier = null;

    /**
     * Constructeur: on charge le devis
     *
     */
    public function __construct() {

        $this->_blockGroup = 'Purchase';
        $this->_objectId = 'id';
        $this->_controller = 'supplier';

        parent::__construct();

        //charge le manufacturer
        $sup_id = Mage::app()->getRequest()->getParam('sup_id', false);
        $model = Mage::getModel('Purchase/Supplier');
        $this->_supplier = $model->load($sup_id);

        $this->setTemplate('Purchase/Supplier/Edit/Tab/Info.phtml');
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
        $retour = '<select id="' . $name . '" name="' . $name . '">';

        //charge la liste des pays
        $collection = Mage::getModel('directory/country')->getCollection()->toOptionArray();
        foreach ($collection as $item) {
            if ($item['value'] == $value)
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item['value'] . '" ' . $selected . '>' . $item['label'] . '</option>';
        }

        $retour .= '</select>';
        return $retour;
    }

    /**
     * Return a combo box with all currencies
     * @param <type> $name
     * @param <type> $value
     * @return string
     */
    public function getCurrencyCombo($name, $value) {
        $retour = '<select id="' . $name . '" name="' . $name . '">';

        //load list
        $options = Mage::app()->getLocale()->getOptionAllCurrencies();

        foreach ($options as $item) {
            if ($item['value'] == $value)
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item['value'] . '" ' . $selected . '>' . $item['label'] . '</option>';
        }

        $retour .= '</select>';
        return $retour;
    }

    public function getTaxRateCombo($name, $value) {
        $retour = '<select id="' . $name . '" name="' . $name . '">';

        //charge la liste des pays
        $options = mage::getSingleton('Purchase/System_Config_Source_Taxrates')->toOptionArray();
        foreach ($options as $item) {
            if ($item['value'] == $value)
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item['value'] . '" ' . $selected . '>' . $item['label'] . '</option>';
        }

        $retour .= '</select>';
        return $retour;
    }

    public function getLocaleCombo($name, $value) {
        $retour = '<select id="' . $name . '" name="' . $name . '">';

        //charge la liste des pays
        $options = mage::getSingleton('adminhtml/system_config_source_locale')->toOptionArray();
        foreach ($options as $item) {
            if ($item['value'] == $value)
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item['value'] . '" ' . $selected . '>' . $item['label'] . '</option>';
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

}
