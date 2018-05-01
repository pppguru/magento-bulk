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
class MDN_Purchase_Block_Product_Edit_Tabs_AssociatedSuppliers extends Mage_Adminhtml_Block_Template {

    private $_currency = null;

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

    private $_showForm = false;

    public function setShowForm($value) {
        $this->_showForm = $value;
        return $this;
    }

    public function getShowForm() {
        return $this->_showForm;
    }

    /**
     * Constructeur
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retourne les fournisseurs associ�s � un produit
     *
     */
    public function getSuppliers() {
        $collection = mage::GetModel('Purchase/ProductSupplier')
                ->getCollection()
                ->join('Purchase/Supplier', 'sup_id=pps_supplier_num')
                ->addFieldToFilter('pps_product_id', $this->getProduct()->getId())
                ->setOrder('pps_last_order_date', 'desc')
        ;
        return $collection;
    }

    /**
     * Retourne la liste des Fournisseurs non li�s au produit sous la forme d'un combo
     *
     */
    public function getNonLinkedSuppliersAsCombo($name = 'supplier') {
        $collection = mage::GetModel('Purchase/ProductSupplier')
                ->getCollection()
                ->addFieldToFilter('pps_product_id', $this->getProduct()->getId())
        ;
        $t_ids = array();
        $t_ids[] = -1;
        foreach ($collection as $item) {
            $t_ids[] = $item->getpps_supplier_num();
        }

        //Recupere la liste
        $collection = mage::GetModel('Purchase/Supplier')
                ->getCollection()
                ->addFieldToFilter('sup_id', array('nin' => $t_ids))
                ->setOrder('sup_name', 'asc');

        //transforme en combo
        $retour = '<select id="' . $name . '" name="' . $name . '">';
        foreach ($collection as $item) {
            $retour .= '<option value="' . $item->getId() . '">' . $item->getsup_name() . '</option>';
        }
        $retour .= '</select>';

        //retour
        return $retour;
    }

    /**
     * Retourne la liste des positionnement de prix sous la forme d'un combo
     *
     */
    public function getPricePositionAsCombo($name = 'price_position') {

        //transforme en combo
        $retour = '<select id="' . $name . '" name="' . $name . '">';
        $retour .= '<option value="unknown">' . $this->__('Unknown') . '</option>';
        $retour .= '<option value="excellent">' . $this->__('Excellent') . '</option>';
        $retour .= '<option value="good">' . $this->__('Good') . '</option>';
        $retour .= '<option value="average">' . $this->__('Average') . '</option>';
        $retour .= '<option value="bad">' . $this->__('Bad') . '</option>';
        $retour .= '</select>';

        //retour
        return $retour;
    }

    /**
     * Retourne l'objet currency li� � la commande
     *
     */
    public function getDefaultCurrency() {
        if ($this->_currency == null) {
            $this->_currency = mage::getModel('directory/currency')->load(Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE));
        }
        return $this->_currency;
    }

}