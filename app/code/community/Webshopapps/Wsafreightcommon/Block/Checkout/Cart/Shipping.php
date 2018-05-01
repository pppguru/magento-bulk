<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*
 * Webshopapps Residential Delivery Extension
 *
 * @author 	Webshopapps
 * @license www.webshopapps.com/license/license.txt
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * (c) Webshopapps.com Zowta Ltd 2010 - All rights reserved.
 */

class Webshopapps_Wsafreightcommon_Block_Checkout_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{

	public function getLiftgateRequired()
    {
    	if(Mage::getStoreConfig('shipping/wsafreightcommon/default_liftgate',Mage::app()->getStore()) && $this->getAddress()->getLiftgateRequired() == ''){
    		return true;
    	} else {
    		return $this->getAddress()->getLiftgateRequired();
    	}
    }

	public function getShiptoType()
    {
        return $this->getAddress()->getShiptoType();
    }

 	public function getCityActive()
    {
    	$active = FALSE;

    	if (!$this->dontShowCommonFreight()) {
    		(bool)Mage::getStoreConfig('carriers/yrcfreight/active')
    		|| (bool)Mage::getStoreConfig('carriers/abffreight/active')
            || (bool)Mage::getStoreConfig('carriers/wsafedexfreight/active') ? $active=TRUE : $active;
    	}

    	if (!$active) {
            if(Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Upsaccesspoint', 'carriers/upsaccesspoint/active')) {
                return true;
            }
    		return parent::getCityActive();
    	} else {
    		return $active;
    	}
    }


  	public function getStateActive()
    {
    	return true;
    }


 /**
     * Check if one of carriers require state/province
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        return true;
    }

    /**
     * Check if one of carriers require city
     *
     * @return bool
     */
    public function isCityRequired()
    {
    	$active = FALSE;

    	(bool)Mage::getStoreConfig('carriers/yrcfreight/active')
    		|| (bool)Mage::getStoreConfig('carriers/abffreight/active')
            || (bool)Mage::getStoreConfig('carriers/wsafedexfreight/active') ? $active=TRUE : $active;

    	if (!$active) {
    		return parent::isCityRequired();
    	} else {
    		return $active;
    	}
    }


    public function getNotifyRequired()
    {
    	return $this->getAddress()->getNotifyRequired();
    }


    public function getInsideDelivery()
    {
        return $this->getAddress()->getInsideDelivery();
    }


	public function getShiptoTypeHtmlSelect($defValue=null) {

		if (is_null($defValue)) {
			$defValue=$this->getShiptoType();
		}

		$options = Mage::helper('wsafreightcommon')->getOptions();

		$html = $this->getLayout()->createBlock('core/html_select')
            ->setName('shipto_type')
            ->setTitle(Mage::helper('wsafreightcommon')->__('Address Type'))
            ->setId('shipto_type')
            ->setClass('required-entry')
            ->setValue($defValue)
            ->setOptions($options)
            ->getHtml();
        return $html;

	}

	public function dontShowCommonFreight() {
		return Mage::helper('wsafreightcommon')->dontShowCommonFreightForQuote(
			$this->getAddress()->getQuote(),$this->getAddress()->getWeight());
	}

    /**
     * Added in for compatibility with AddressValidator
     * @param null $defValue
     * @return mixed
     */
    public function getDestTypeHtmlSelect($defValue=null) {

        if(Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsavalidation')){
            if (is_null($defValue)) {
                $defValue=$this->getAddress()->getDestType();
            }

            return Mage::helper('wsavalidation')->getBasicDestTypeHtmlSelect($this->getLayout(),$defValue);
        }
        return null;
    }
}