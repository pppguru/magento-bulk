<?php

 /**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Common
 * User         Joshua Stewart
 * Date         20/09/2013
 * Time         11:56
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

class Webshopapps_Wsacommon_Helper_Template extends Mage_Core_Helper_Abstract
{
    public function adminTemplate($ref)
    {
        switch ($ref) {
            case 'adminhtml_sales_order_create_index';
            case 'adminhtml_sales_order_create_load_block_data';
            case 'adminhtml_sales_order_create_load_block_shipping_method';

            if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsafreightcommon',
                                                           'shipping/wsafreightcommon/active') &&
                Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Adminshipping')) {
                return 'webshopapps/wsafreightcommon/sales/order/create/shipping/method/formcombineadmin.phtml';
            }

            if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Calendarbase',
                                                           'shipping/webshopapps_dateshiphelper/active') &&
                Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Adminshipping')) {
                return 'webshopapps_adminshipping/order/create/shipping/method/formcombined.phtml';
            }

            if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsafreightcommon',
                                                           'shipping/wsafreightcommon/active')) {
                return 'webshopapps/wsafreightcommon/sales/order/create/shipping/method/form.phtml';
            }
            //AS-6
            if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Calendarbase',
                                                           'shipping/webshopapps_dateshiphelper/active')) {
                return 'webshopapps/calendarbase/sales/order/create/shipping/method/form.phtml';
            }

            if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Adminshipping')) {
                return 'webshopapps_adminshipping/order/create/shipping/method/form.phtml';
            }

            break;
        }

        return "";
    }

    public function onepageShippingMethod()
    {
        $insuranceActive = Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Insurance','shipping/insurance/active');
        $dropshipAvtve = Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Dropship','carriers/dropship/active');

        if ($insuranceActive && !$dropshipAvtve) {
            return 'webshopapps/insurance/checkout/onepage/shipping/method/available.phtml';
        }

        return 'webshopapps/dropship/checkout/onepage/shipping_method/available.phtml';
    }

    public function cartShippingEstimate()
    {
        $startrack = Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Startrack','carriers/startrack/active');
        $dimensionalDebug = Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa', 'shipping/shipusa/active') &&
            (Mage::helper('wsalogger')->isDebugError() || Mage::getStoreConfig('shipping/shipusa/is_demo'));
        $version = Mage::helper('wsacommon')->getNewVersion();

        if ($startrack) {
            if ($version >= 14) {
                return 'webshopapps/startrack/cart/rwd/shipping.phtml';
            }
            else {
                return 'webshopapps/startrack/cart/shipping.phtml';
            }
        }

        else if($dimensionalDebug) {
            if ($version >= 14) {
                return 'webshopapps_shipusa/cart/rwd/shipping.phtml';
            }
            else {
                return 'webshopapps_shipusa/cart/shipping.phtml';
            }
        }

        return 'checkout/cart/shipping.phtml';
    }
}