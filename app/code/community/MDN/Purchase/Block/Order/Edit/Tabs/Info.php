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
class MDN_Purchase_Block_Order_Edit_Tabs_Info extends Mage_Adminhtml_Block_Widget_Form {

    private $_order = null;
    private $_supplier = null;

    /**
     * Constructeur: on charge
     *
     */
    public function __construct() {

        $this->_blockGroup = 'Purchase';
        $this->_objectId = 'id';
        $this->_controller = 'order';

        parent::__construct();

        //charge le manufacturer
        $po_num = Mage::app()->getRequest()->getParam('po_num', false);
        $model = Mage::getModel('Purchase/Order');
        $this->_order = $model->load($po_num);
        $this->_supplier = mage::getModel('Purchase/Supplier')->load($this->_order->getpo_sup_num());

        $this->setTemplate('Purchase/Order/Edit/Tab/Info.phtml');
    }

    /**
     * Retourne l'url pour delete
     *
     */
    public function getDeleteUrl() {
        return $this->getUrl('adminhtml/Purchase_Orders/Delete') . 'po_num/' . $this->getOrder()->getId();
    }

    /**
     * Retourne l'objet
     *
     * @return unknown
     */
    public function getOrder() {
        return $this->_order;
    }

    /**
     * Retourne le fournisseur
     *
     */
    public function getSupplier() {
        return $this->_supplier;
    }

    /**
     * Retourne la liste des devises sous la forme d'un combo
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getCurrencyAsCombo($name = 'currency', $value = '', $style ='') {
        $retour = '<select  id="' . $name . '" name="' . $name . '" style="' . $style . '">';
        $collection = Mage::app()->getLocale()->getOptionAllCurrencies();
        foreach ($collection as $item) {
            if ($value == $item['value'])
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item['value'] . '" ' . $selected . '>' . $item['label'] . '</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    /**
     * Retourne la liste des transporteur sous la forme d'un combo
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getCarriersAsCombo($name = 'carriers', $value = '', $style ='') {
        $retour = '<select  id="' . $name . '" name="' . $name . '" style="' . $style . '">';
        $collection = explode(',', Mage::getStoreConfig('purchase/configuration/order_carrier'));
        foreach ($collection as $item) {
            if (strtolower($value) == strtolower($item))
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    /**
     * Retourne la liste des modes de paiement sous la forme d'un combo
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getPaymentModeAsCombo($name = 'carriers', $value = '', $style ='') {
        $retour = '<select  id="' . $name . '" name="' . $name . '" style="' . $style . '">';
        $collection = explode(',', Mage::getStoreConfig('purchase/configuration/order_payment_method'));
        foreach ($collection as $item) {
            if (strtolower($value) == strtolower($item))
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    /**
     * Return statuses as combo
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getStatusAsCombo($name, $defaultValue = '', $style ='') {
        $retour = '<select  id="' . $name . '" name="' . $name . '" style="' . $style . '">';
        $statuses = $this->getOrder()->getStatuses();
        foreach ($statuses as $key => $value) {
            if ($key == $defaultValue)
                $selected = ' selected ';
            else
                $selected = '';
            $retour .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    /**
     * Return supplier list as combobox
     */
    public function getSuppliersAsCombo($name = 'supplier', $value, $style ='') {
        $retour = '<select  id="' . $name . '" name="' . $name . '" style="' . $style . '">';

        //charge la liste des pays
        $collection = Mage::getModel('Purchase/Supplier')
                ->getCollection()
                ->setOrder('sup_name', 'asc');
        foreach ($collection as $item) {
            $selected = '';
            if ($item->getsup_id() == $value)
                $selected = ' selected ';
            $retour .= '<option value="' . $item->getsup_id() . '" ' . $selected . '>' . $item->getsup_name() . '</option>';
        }

        $retour .= '</select>';
        return $retour;
    }

    /**
     * return warehouse list as combo
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getWarehousesAsCombo($name, $value, $style = '') {
        $html = '<select  id="' . $name . '" name="' . $name . '" style="' . $style . '">';
        $collection = Mage::getModel('AdvancedStock/Warehouse')->getCollection();
        foreach ($collection as $item) {
            $selected = ($value == $item->getId())?' selected ':'';
            $html .= '<option value="' . $item->getId() . '" ' . $selected . '>' . $item->getstock_name() . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Return alert for carriage free and order minimum amount
     */
    public function getNotices() {
        $notices = array();

        //display notices (if enabled)
        if (mage::getStoreConfig('purchase/purchase_order/display_order_notices')) {
            $order = $this->getOrder();
            if (!$order->isFreeCarriageForWeight())
                $notices[] = ($this->__('Order weight (%s) doesnt fullfill free carriage weight condition (%s)', $order->getWeight(), $order->getSupplier()->getsup_free_carriage_weight()));
            if (!$order->isFreeCarriageForAmount()) {
                $freeCarriageAmount = $order->getSupplier()->getsup_free_carriage_amount();
                $freeCarriageAmount = $freeCarriageAmount * $order->getpo_currency_change_rate();
                $freeCarriageAmount = $order->getCurrency()->formatTxt($freeCarriageAmount);
                $notices[] = ($this->__('Order total (%s) doesnt fullfill free carriage total condition (%s)', $order->getCurrency()->formatTxt($order->getProductTotal()), $freeCarriageAmount));
            }
            if (!$order->reachOrderMinimumAmount()) {
                $minimumOfOrder = $order->getSupplier()->getsup_order_mini();
                $minimumOfOrder = $minimumOfOrder * $order->getpo_currency_change_rate();
                $minimumOfOrder = $order->getCurrency()->formatTxt($minimumOfOrder);
                $notices[] = ($this->__('Order total (%s) doesnt fullfill minimum amount condition (%s)', $order->getCurrency()->formatTxt($order->getProductTotal()), $minimumOfOrder));
            }
        }

        return $notices;
    }

    /**
     * Return magento base currency
     */
    public function getBaseCurrencyCode() {
        return Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        ;
    }

    /**
     * Get a colored delivery percent
     */
    public function getDeliveryPercent() {        
        $color = 'black';
        $percent = $this->getOrder()->getpo_delivery_percent();
        if($percent>100){
            $color = 'red';
        }
        if($percent==100){
            $color = 'green';
        }
        $html = '<b><font color="'.$color.'">'.$percent.'%</font></b>';
        return $html;
    }

    public function getBooleanCombo($name, $defaultValue, $style = '') {
        $values = array('0' => $this->__('No'), '1' => $this->__('yes'));
        $html = '<select name="' . $name . '" id="' . $name . '"  style="' . $style . '">';
        foreach ($values as $key => $label) {
            $selected = '';
            if ($key == $defaultValue)
                $selected = ' selected ';
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

}
