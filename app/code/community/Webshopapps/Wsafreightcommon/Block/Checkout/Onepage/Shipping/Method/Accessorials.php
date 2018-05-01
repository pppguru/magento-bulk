<?php

/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_$(PROJECT_NAME)
 * User         joshstewart
 * Date         05/07/2013
 * Time         12:14
 * @copyright   Copyright (c) $(YEAR) Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, $(YEAR), Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

class Webshopapps_Wsafreightcommon_Block_Checkout_Onepage_Shipping_Method_Accessorials
    extends Mage_Checkout_Block_Onepage_Abstract
{

    public function showAccessorials()
    {
        return !$this->helper('wsafreightcommon')->dontShowCommonFreightForQuote($this->getQuote());

    }

    public function isOSCEnabled()
    {
        if (Mage::helper('wsacommon')->isModuleEnabled('Idev_OneStepCheckout',
            'onestepcheckout/general/rewrite_checkout_links')
        ) {
            $oscEnabled = true;
        } else {
            $oscEnabled = false;
        }
        return $oscEnabled;
    }

    public function isIWDEnabled()
    {
        if (Mage::helper('wsacommon')->isModuleEnabled('IWD_OnepageCheckout','onepagecheckout/general/enabled')) {
            $iwdEnabled = true;
        } else {
            $iwdEnabled = false;
        }
        return $iwdEnabled;
    }

    public function isInsuranceEnabled()
    {
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Insurance', 'shipping/insurance/active')) {
            return true;
        }

        return false;
    }

    public function getLiftgateRequired()
    {
        $liftgateRequired = $this->getQuote()->getShippingAddress()->getLiftgateRequired();

        if (Mage::helper('wsafreightcommon')->getDefaultLiftgate() &&
            empty($liftgateRequired)) {
            return true;
        } else {
            return empty($liftgateRequired) ? false : true;
        }
    }

    public function getNotifyRequired()
    {
        $notifyRequired = $this->getQuote()->getShippingAddress()->getNotifyRequired();;
        if (is_null($notifyRequired)) {
            return false;
        }
        return $notifyRequired;
    }

    public function getInsideDelivery()
    {
        $insideDelivery = $this->getQuote()->getShippingAddress()->getInsideDelivery();;
        if (is_null($insideDelivery)) {
            return false;
        }
        return $insideDelivery;
    }

    public function getShiptoType()
    {
        return $this->getQuote()->getShippingAddress()->getShiptoType();
    }

    public function getShiptoTypeHtmlSelect($carrierCode, $warehouseInsert = null, $defValue = null)
    {

        if (is_null($defValue)) {
            $defValue = Mage::getStoreConfig('shipping/wsafreightcommon/default_address');
        }

        $options = Mage::helper('wsafreightcommon')->getOptions();
        $id = 'shipto_type_' .$carrierCode .$warehouseInsert;
        $html = $this->getLayout()->createBlock('core/html_select')
            ->setName($id)
            ->setTitle(Mage::helper('wsafreightcommon')->__('Address Type'))
            ->setId($id)
            ->setClass('required-entry accessorial')
            ->setValue($defValue)
            ->setOptions($options)
            ->getHtml();
        return $html;

    }

    /**
     * @return string formatted with carriage returns to <br /> tag
     */

    public function getCustomDescription()
    {
        return Mage::helper('wsafreightcommon')->getCustomDescription();
    }

    public function getLiftgateLabel($carrierCode)
    {
        if(!Mage::helper('wsafreightcommon')->getUseLiveAccessories()
            && Mage::getStoreConfig('shipping/wsafreightcommon/show_liftgate_fee')) {
            $price = $this->getQuote()->getStore()->convertPrice(
                Mage::helper('wsafreightcommon')->getLiftgateFee($carrierCode), true);
            return Mage::helper('wsafreightcommon')->__("Add Liftgate (+$price)");
        }
        return Mage::helper('wsafreightcommon')->__('Do you need a Liftgate?');;
    }

}