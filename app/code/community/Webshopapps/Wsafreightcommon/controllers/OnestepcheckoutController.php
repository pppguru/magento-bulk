<?php

/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_onestepfreight
 * User         Joshua Stewart
 * Date         25/10/2013
 * Time         12:58
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */
require_once 'Idev/OneStepCheckout/controllers/AjaxController.php';


class Webshopapps_Wsafreightcommon_OnestepcheckoutController extends Idev_OneStepCheckout_AjaxController
{
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('onestepcheckout_ajax_save_billing');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();

        return $output;
    }

    public function getFreightAction()
    {
        if ($this->getRequest()->isGet()) {
            $liftgateRequired = $this->getRequest()->getParam('liftgate_required') == "true" ? true : false;
            $notifyRequired = $this->getRequest()->getParam('notify_required') == "true" ? true : false;
            $insideDelivery = $this->getRequest()->getParam('inside_delivery') == "true" ? true : false;
            $shiptoType = $this->getRequest()->getParam('shipto_type');
        } else {
            $liftgateRequired = false;
            $notifyRequired = false;
            $insideDelivery = false;
            $shiptoType = 0;
        }

        if (Mage::helper('wsacommon')->isModuleEnabled('Idev_OneStepCheckout', 'onestepcheckout/general/rewrite_checkout_links')) {
            $this->_getOnepage()->getQuote()->getBillingAddress()->setLiftgateRequired($liftgateRequired);
            $this->_getOnepage()->getQuote()->getBillingAddress()->setNotifyRequired($notifyRequired);
            $this->_getOnepage()->getQuote()->getBillingAddress()->setInsideDelivery($insideDelivery);
            $this->_getOnepage()->getQuote()->getBillingAddress()->setShiptoType($shiptoType);
        }

        $this->_getOnepage()->getQuote()->getShippingAddress()->setLiftgateRequired($liftgateRequired);
        $this->_getOnepage()->getQuote()->getShippingAddress()->setNotifyRequired($notifyRequired);
        $this->_getOnepage()->getQuote()->getShippingAddress()->setInsideDelivery($insideDelivery);
        $this->_getOnepage()->getQuote()->getShippingAddress()->setShiptoType($shiptoType);

        $this->_getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        $this->_getOnepage()->getQuote()->save();

        $this->_getOnepage()->getQuote()->getShippingAddress()->collectShippingRates()->save();

        $result = $this->_getShippingMethodsHtml();

        $this->getResponse()->setBody($result);
    }

    protected function _getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
}