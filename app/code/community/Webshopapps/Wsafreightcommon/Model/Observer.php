<?php

/**
 * Magento Webshopapps Module
 *
 * @category   Webshopapps
 * @package    Webshopapps Wsafreightcommon
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
 */

class Webshopapps_Wsafreightcommon_Model_Observer extends Mage_Core_Model_Abstract
{
    public function postError($observer)
    {

        $allFreightCarriers = Mage::helper('wsafreightcommon')->getAllFreightCarriers();

        if (in_array('yrcfreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMveXJjZnJlaWdodC9zaGlwX29uY2U=', 'eXVtbXlnbGFzcw==', 'Y2FycmllcnMveXJjZnJlaWdodC9zZXJpYWw=')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIFlSQyBGcmVpZ2h0')));
            }
        }
        if (in_array('wsaupsfreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NhdXBzZnJlaWdodC9zaGlwX29uY2U=', 'b25zaWRl', 'Y2FycmllcnMvd3NhdXBzZnJlaWdodC9zZXJpYWw=')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIFVQUyBGcmVpZ2h0')));
            }
        }
        if (in_array('wsafedexfreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NhZmVkZXhmcmVpZ2h0L3NoaXBfb25jZQ==', 'd2Fyd29ybGQ=', 'Y2FycmllcnMvd3NhZmVkZXhmcmVpZ2h0L3NlcmlhbA==')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIEZlZGV4IEZyZWlnaHQ=')));
            }
        }
        if (in_array('rlfreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvcmxmcmVpZ2h0L3NoaXBfb25jZQ==', 'd2luZG93bW9vbg==', 'Y2FycmllcnMvcmxmcmVpZ2h0L3NlcmlhbA==')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIFJMIEZyZWlnaHQ=')));
            }
        }
        if (in_array('echofreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvZWNob2ZyZWlnaHQvc2hpcF9vbmNl', 'd2VuZHlob3VzZQ==', 'Y2FycmllcnMvZWNob2ZyZWlnaHQvc2VyaWFs')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('CVNlcmlhbCBLZXkgSXMgTk9UIFZhbGlkIGZvciBXZWJTaG9wQXBwcyBFY2hvIEZyZWlnaHQ=')));
            }
        }
        if (in_array('abffreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvYWJmZnJlaWdodC9zaGlwX29uY2U=', 'YWJmdXBzaWRl', 'Y2FycmllcnMvYWJmZnJlaWdodC9zZXJpYWw=')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('CVNlcmlhbCBLZXkgSXMgTk9UIFZhbGlkIGZvciBXZWJTaG9wQXBwcyBBQkYgRnJlaWdodA==')));
            }
        }
        if (in_array('newgistics', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvbmV3Z2lzdGljcy9zaGlwX29uY2U=', 'aHVsa3NtYXNo', 'Y2FycmllcnMvbmV3Z2lzdGljcy9zZXJpYWw=')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIE5ld2dpc3RpY3MgRnJlaWdodA==')));
            }
        }
        if (in_array('conwayfreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvY29ud2F5ZnJlaWdodC9zaGlwX29uY2U=', 'aGVyZWlhbQ==', 'Y2FycmllcnMvY29ud2F5ZnJlaWdodC9zZXJpYWw=')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIENvbi1XYXkgRnJlaWdodA==')));
            }
        }
        if (in_array('estesfreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvZXN0ZXNmcmVpZ2h0L3NoaXBfb25jZQ==', 'b3ZlcmxhbmRlcg==', 'Y2FycmllcnMvZXN0ZXNmcmVpZ2h0L3NlcmlhbA==')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIEVzdGVzIEZyZWlnaHQ=')));
            }
        }
        if (in_array('cerasisfreight', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvY2VyYXNpc2ZyZWlnaHQvc2hpcF9vbmNl', 'Z3JpenpseWJlYXI=', 'Y2FycmllcnMvY2VyYXNpc2ZyZWlnaHQvc2VyaWFs')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIENlcmFzaXMgRnJlaWdodA==')));
            }
        }
        if (in_array('wsaolddominion', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3Nhb2xkZG9taW5pb24vc2hpcF9vbmNl', 'Ymx1ZWxpemFyZA=', 'Y2FycmllcnMvd3Nhb2xkZG9taW5pb24vc2VyaWFs')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIE9sZCBEb21pbmlvbiBGcmVpZ2h0==')));
            }
        }
        if (in_array('prostar', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvcHJvc3Rhci9zaGlwX29uY2U=', 'YW1hemluZ2dyYWNl', 'Y2FycmllcnMvcHJvc3Rhci9zZXJpYWw=')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIFByb3N0YXIgRnJlaWdodA==')));
            }
        }
        if (in_array('wsayrcholland', $allFreightCarriers)) {

            if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvd3NheXJjaG9sbGFuZC9zaGlwX29uY2U=', 'dXBzaWRlZnJvbnQ', 'Y2FycmllcnMvd3NheXJjaG9sbGFuZC9zZXJpYWw=')) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIFlSQyBIb2xsYW5kIEZyZWlnaHQ=')));
            }
        }
    }

    public function hookToControllerActionPreDispatch($observer)
    {
        $actionName = $observer->getEvent()->getControllerAction()->getFullActionName();

        $actionNames = array('checkout_cart_estimatePost','loworderfee_cart_estimatePost','checkout_icart_updateRegion');

        //we compare action name to see if that's action for which we want to add our own event
        if (in_array($actionName, $actionNames)) {
            $request = $observer->getControllerAction()->getRequest();
            $country = (string)$request->getParam('country_id');
            $postcode = (string)$request->getParam('estimate_postcode');
            $city = (string)$request->getParam('estimate_city');
            $regionId = (string)$request->getParam('region_id');
            $region = (string)$request->getParam('region');
            $liftgateRequired = (string)$request->getParam('liftgate_required');
            $notifyRequired = (string)$request->getParam('notify_required');
            $insideDelivery = (string)$request->getParam('inside_delivery');
            $shiptoType = (string)$request->getParam('shipto_type');


            if ($request->getParam('dest_type') == '') {
                if(Mage::getStoreConfig('shipping/wsafreightcommon/default_address')) {
                    $destType = Mage::getSingleton('wsafreightcommon/freightCommon')->getCode('dest_type_reverse', $shiptoType);
                } else {
                    $destType = Mage::getSingleton('wsafreightcommon/freightCommon')->getCode('dest_type', $shiptoType);
                }
            } else {
                $destType = (string)$request->getParam('dest_type');
            }

            $this->_getQuote()->getShippingAddress()
                ->setCountryId($country)
                ->setCity($city)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region)
                ->setLiftgateRequired($liftgateRequired)
                ->setShiptoType($shiptoType)
                ->setDestType($destType)
                ->setNotifyRequired($notifyRequired)
                ->setInsideDelivery($insideDelivery)
                ->setCollectShippingRates(true);
            $this->_getQuote()->getShippingAddress()->save();
        }

    }

    public function hookToControllerActionPostDispatch($observer)
    {

        $orderStore = $this->_getQuote()->getStore();
        $showCheapest = Mage::getStoreConfig('shipping/wsafreightcommon/auto_select_cheapest', $orderStore);

        if ($showCheapest) {

            $actionName = $observer->getEvent()->getControllerAction()->getFullActionName();

            $actionNames = array('checkout_cart_estimatePost',
                                 'checkout_cart_add',
                                 'checkout_cart_updatePost',
                                 'checkout_onepage_saveShippingExtra',
                                 'loworderfee_cart_estimatePost',
                                 'checkout_icart_updateRegion'
                );

            if (in_array($actionName, $actionNames)) {
                $method = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();

                if (!empty($method)) {
                    return;
                }

                $rates = Mage::getSingleton('checkout/session')->getQuote()
                    ->getShippingAddress()->getGroupedAllShippingRates();

                if (!count($rates)) {
                    Mage::getSingleton('checkout/session')->getQuote()
                        ->getShippingAddress()->collectShippingRates();

                    $rates = Mage::getSingleton('checkout/session')->getQuote()
                        ->getShippingAddress()->getGroupedAllShippingRates();
                }

                if (count($rates)) {
                    $topRate = null;
                    foreach ($rates as $rateArray) {
                        $cheapest = reset($rateArray);

                        if ($topRate) {
                            if ($cheapest->getPrice() < $topRate->getPrice()) {
                                $topRate = $cheapest;
                            }
                        } else {
                            $topRate = $cheapest;
                        }
                    }

                    $code = $topRate->code;

                    try {
                        Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()
                            ->setShippingMethod($code);

                        Mage::getSingleton('checkout/session')->getQuote()->save();

                        Mage::getSingleton('checkout/session')->resetCheckout();

                        unset($cheapest);
                        unset($topRate);

                    } catch (Mage_Core_Exception $e) {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addException(
                            $e, Mage::helper('checkout')->__('Load customer quote error')
                        );
                    }
                }
            }
        }
    }

    public function hookToControllerSaveShippingMethod($observer)
    {
        $shippingMethodDetails = $observer->getControllerAction()->getRequest()->getPost();
        if(!array_key_exists('shipping_method', $shippingMethodDetails)) {
            return;
        }
        $shippingMethod = $shippingMethodDetails['shipping_method'];
        $shipMethodArray = explode( '_', $shippingMethod);
        $carrierCode = $shipMethodArray[0];
        if($carrierCode) {

            $liftgateRequired = array_key_exists('liftgate_required_'.$carrierCode, $shippingMethodDetails) ?
                $shippingMethodDetails['liftgate_required_'.$carrierCode] :  false;
            $notifyRequired = array_key_exists('notify_required_'.$carrierCode, $shippingMethodDetails) ?
                $shippingMethodDetails['notify_required_'.$carrierCode] :  false;
            $insideDelivery = array_key_exists('inside_delivery_'.$carrierCode, $shippingMethodDetails) ?
                $shippingMethodDetails['inside_delivery_'.$carrierCode] :  false;
            $shiptoType = array_key_exists('shipto_type_'.$carrierCode, $shippingMethodDetails) ?
                $shippingMethodDetails['shipto_type_'.$carrierCode] :  false;

            if(Mage::getStoreConfig('shipping/wsafreightcommon/default_address')) {
                $destType = Mage::getSingleton('wsafreightcommon/freightCommon')->getCode('dest_type_reverse', $shiptoType);
                $shiptoType == 0 || $shiptoType == '' ? $shiptoType = 1 : $shiptoType = 0;
            } else {
                $destType = Mage::getSingleton('wsafreightcommon/freightCommon')->getCode('dest_type', $shiptoType);
            }

            if(Mage::helper('wsacommon')->isModuleEnabled
                ('Idev_OneStepCheckout', 'onestepcheckout/general/rewrite_checkout_links')) {

                $billingAddress = $this->_getQuote()->getBillingAddress();
                $billingAddress->setLiftgateRequired($liftgateRequired)
                    ->setNotifyRequired($notifyRequired)
                    ->setInsideDelivery($insideDelivery)
                    ->setShiptoType($shiptoType)
                    ->setDestType($destType)
                    ->save();
            }

            $address =  $this->_getQuote()->getShippingAddress();
            $address->setLiftgateRequired($liftgateRequired)
                ->setNotifyRequired($notifyRequired)
                ->setInsideDelivery($insideDelivery)
                ->setDestType($destType)
                ->setShiptoType($shiptoType);

            $address->save();
        }

    }

    public function hookToAdminSalesOrderCreateProcessDataBefore($observer)
    {
        if ($observer->getRequestModel()->getPost('collect_shipping_rates')) {
            $freightDetails = $observer->getRequestModel()->getPost('freight');

            if (!empty($freightDetails)) {
                array_key_exists('liftgate_required', $freightDetails) ? $liftgateRequired = (string)$freightDetails['liftgate_required'] : $liftgateRequired = '';
                array_key_exists('notify_required', $freightDetails) ? $notifyRequired = (string)$freightDetails['notify_required'] : $notifyRequired = '';
                array_key_exists('inside_delivery', $freightDetails) ? $insideDelivery = (string)$freightDetails['inside_delivery'] : $insideDelivery = '';
                array_key_exists('shipto_type', $freightDetails) ? $shiptoType = (string)$freightDetails['shipto_type'] : $shiptoType = '';
                array_key_exists('dest_type', $freightDetails) ? $destType = (string)$freightDetails->getParam('dest_type') : $destType = '';

                if ($destType == '') {
                    if(Mage::getStoreConfig('shipping/wsafreightcommon/default_address')) {
                        $destType = Mage::getSingleton('wsafreightcommon/freightCommon')->getCode('dest_type_reverse', $shiptoType);
                    } else {
                        $destType = Mage::getSingleton('wsafreightcommon/freightCommon')->getCode('dest_type', $shiptoType);
                    }
                }

                $this->_getAdminQuote()->getShippingAddress()
                    ->setShiptoType($shiptoType)
                    ->setDestType($destType)
                    ->setNotifyRequired($notifyRequired)
                    ->setInsideDelivery($insideDelivery)
                    ->setLiftgateRequired($liftgateRequired);
                $this->_getAdminQuote()->getShippingAddress()->save();

                $this->_getAdminQuote()->getBillingAddress()
                    ->setShiptoType($shiptoType)
                    ->setDestType($destType)
                    ->setNotifyRequired($notifyRequired)
                    ->setInsideDelivery($insideDelivery)
                    ->setLiftgateRequired($liftgateRequired);
                $this->_getAdminQuote()->getBillingAddress()->save();
            }
        }

    }


    public function saveFreightQuoteId($observer)
    {

        $address = $observer->getEvent()->getQuoteAddress();

        $method = $address->getShippingMethod();

        foreach ($address->getAllShippingRates() as $rate) {
            if ($rate->getCode() == $method) {
                $methodDesc = $rate->getMethodDescription();
                if (!empty($methodDesc)) {
                    if(substr($methodDesc, 0, 2) == 'c-') {
                        $methodDesc = ltrim($methodDesc, 'c-');
                        $address->setOriginalShippingMethod($methodDesc);
                    } else {
                        $address->setFreightQuoteId($rate->getMethodDescription());
                    }
                } else {
                    $address->setFreightQuoteId('');
                }
                $address->save();
                break;
            }
        }

        return $this;

    }

    public function addNewLayout($observer)
    {
        $layout = $observer->getEvent()->getLayout();
        $update = $layout->getUpdate();

        $action = $observer->getEvent()->getAction();
        $fullActionName = $action->getFullActionName();

        switch ($fullActionName) {
            case 'adminhtml_sales_order_view':
                // only execute if Dropship in-active
                if (!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Dropcommon', 'carriers/dropship/active') &&
                    !Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Dropship', 'carriers/dropship/active'))
                {
                    $xml = '<reference name="order_tab_info"><action method="setTemplate"> <template>webshopapps/wsafreightcommon/sales/order/view/tab/info_container.phtml</template> </action>
                <block type="wsafreightcommon/adminhtml_sales_order_view_freightinfo" name="wsafreightcommon_info" template="webshopapps/wsafreightcommon/sales/order/view/wsafreightcommon_info.phtml"/>
                <block type="adminhtml/sales_order_view_tab_info" name="order_info_orig" template="sales/order/view/tab/info.phtml">
                    <block type="adminhtml/sales_order_view_messages" name="order_messages"/>
                    <block type="adminhtml/sales_order_view_info" name="order_info" template="sales/order/view/info.phtml"/>
                    <block type="adminhtml/sales_order_view_items" name="order_items" template="sales/order/view/items.phtml">
                        <action method="addItemRender"><type>default</type><block>adminhtml/sales_order_view_items_renderer_default</block><template>sales/order/view/items/renderer/default.phtml</template></action>
                        <action method="addColumnRender"><column>qty</column><block>adminhtml/sales_items_column_qty</block><template>sales/items/column/qty.phtml</template></action>
                        <action method="addColumnRender"><column>name</column><block>adminhtml/sales_items_column_name</block><template>sales/items/column/name.phtml</template></action>
                        <action method="addColumnRender"><column>name</column><block>adminhtml/sales_items_column_name_grouped</block><template>sales/items/column/name.phtml</template><type>grouped</type></action>
                    ';
                    if (Mage::helper("wsacommon")->isEnterpriseEdition()) {

                        $xml .= '<action method="addColumnRender"><column>name</column><block>enterprise_giftcard/adminhtml_sales_items_column_name_giftcard</block><template>sales/items/column/name.phtml</template><type>giftcard</type></action>';
                    }

                    $xml .= '<block type="core/text_list" name="order_item_extra_info" />
                        </block>
                        <block type="adminhtml/sales_order_payment" name="order_payment"/>
                        <block type="adminhtml/sales_order_view_history" name="order_history" template="sales/order/view/history.phtml"/>';

                    if (Mage::helper("wsacommon")->isEnterpriseEdition()) {
                        $xml .= '<block type="adminhtml/template" name="gift_options" template="sales/order/giftoptions.phtml">
                                  <block type="adminhtml/sales_order_view_giftmessage" name="order_giftmessage" template="sales/order/view/giftmessage.phtml"/></block>';
                    }

                    $xml .= '
                                <block type="adminhtml/sales_order_totals" name="order_totals" template="sales/order/totals.phtml">
                                <block type="adminhtml/sales_order_totals_tax" name="tax" template="sales/order/totals/tax.phtml" />';

                    if (Mage::helper("wsacommon")->isEnterpriseEdition()) {
                        $xml .= '<block type="adminhtml/sales_order_totals_item" name="giftcardaccount" template="enterprise/giftcardaccount/sales/order/totals/giftcardaccount.phtml">
                                 <action method="setBeforeCondition"><param>customerbalance</param></action>
                                 </block>';
                    }

                    $xml .= '</block>
                             </block>
                             </reference>';

                    $update->addUpdate($xml);
                }

                break;
        }

        return;
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getAdminSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    protected function _getAdminQuote()
    {
        return $this->_getAdminSession()->getQuote();
    }
}