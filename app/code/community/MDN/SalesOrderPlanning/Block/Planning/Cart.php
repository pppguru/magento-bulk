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
class MDN_SalesOrderPlanning_Block_Planning_Cart extends Mage_Checkout_Block_Cart_Abstract {

    private $_planning = null;


    protected function _construct() {
        parent::_construct();
        $this->refresh($this->getPlanning());
    }

    public function refresh($planning){
        $quote = $this->getQuoteForPlanning();
        if ($quote) {
            $this->refreshDates($quote,$planning);
        }
    }

    public function getQuoteForPlanning(){
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function refreshDates($quote,$planning){
        $quote->setanounced_date($planning->getpsop_anounced_date());
        $quote->setanounced_date_max($planning->getpsop_anounced_date_max());
        $quote->save();
    }

    /**
     * Return delivery msg
     *
     */
    public function getDeliveryMsg() {

        $planning = $this->getPlanning();

        $this->refresh($planning);

        $deliveryDate = mage::helper('core')->formatDate($planning->getpsop_anounced_date(), 'medium');
        $deliveryMaxDate = mage::helper('core')->formatDate($planning->getpsop_anounced_date_max(), 'medium');

        //define message
        $html = '<div>';
        $html .= mage::helper('SalesOrderPlanning')->__('Your order should be delivered on <b>%s</b>', $deliveryDate);
        $html .= '<br>' . mage::helper('SalesOrderPlanning')->__('At worst, we commit you deliver on <b>%s</b>', $deliveryMaxDate);
        $html .= '</div>';

        return $html;
    }

    /**
     * Return planning object
     *
     * @return unknown
     */
    public function getPlanning() {
        if ($this->_planning == null) {
            $this->_planning = mage::helper('SalesOrderPlanning/Planning')->getEstimationForQuote($this->getQuote());
        }
        return $this->_planning;
    }


    /**
     * Return comments
     *
     * @return unknown
     */
    public function getComments() {
        $html = $this->getPlanning()->getpsop_consideration_comments() . '<br>';
        $html.= $this->getPlanning()->getpsop_fullstock_comments() . '<br>';
        $html.= $this->getPlanning()->getpsop_shipping_comments() . '<br>';
        $html.= $this->getPlanning()->getpsop_delivery_comments() . '<br>';
        return $html;
    }


    public function isPlanningEnabled()
    {
        return Mage::getStoreConfig('planning/general/enable_planning') == 1 ? true: false;
    }

    public function isShippingMethodSelected()
    {
        return $this->getQuote()->getShippingAddress()->getShippingMethod();
    }
}