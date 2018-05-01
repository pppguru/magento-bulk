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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_UpsWorldship extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract {

    /**
     * Generate XML output
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection) {
        //init xml writer and write directives
        $xml = mage::helper('Orderpreparation/XmlWriter');
        $xml->init();
        $xml->push('OpenShipments', array('xmlns' => 'x-schema:OpenShipments.xdr'));

        //browse collection
        foreach ($orderPreparationCollection as $orderToPrepare) {

            //check shipping method
            $order = mage::getModel('sales/order')->load($orderToPrepare->getorder_id());
            if (!$this->checkShippingMethod($order))
                    continue;

            //init info for order
            $xml->push('OpenShipment', array('ShipmentOption' => '', 'ProcessStatus' => ''));
            $shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($orderToPrepare->getshipment_id());
            $address = $this->getAddress($order);

            //ship to section
            $xml->push('ShipTo');

            $xml->element('CustomerID', $order->getcustomer_id());
            $xml->element('CompanyOrName', $address->getname());
            $xml->element('Attention', $shipment->getincrement_id());
            $xml->element('Address1', $address->getStreet(1));
            $xml->element('Address2', $address->getStreet(2));
            $xml->element('CountryTerritory', $address->getCountry());
            $xml->element('PostalCode', $address->getPostcode());
            $xml->element('CityOrTown', $address->getcity());

            $region = Mage::getModel('directory/region')->load($address->getregion_id());
            $xml->element('StateProvinceCounty', $region->getCode());

            $xml->element('Telephone', $address->gettelephone());
            $xml->element('EmailAddress', $order->getCustomerEmail());

            $xml->pop();

            //shipment info
            $xml->push('ShipmentInformation');

            $xml->element('ServiceType', $this->getServiceType($order));
            $xml->element('PackageType', Mage::getStoreConfig('carriers/ups/container'));
            $xml->element('NumberOfPackages', $orderToPrepare->getpackage_count());
            $xml->element('ShipmentActualWeight', $orderToPrepare->getreal_weight());
            $xml->element('DescriptionOfGoods', Mage::getStoreConfig('orderpreparation/ups/description_of_good'));
            $xml->element('Reference1', $order->getincrement_id());
            $xml->element('DocumentOnly', '0');
            $xml->element('GoodsNotInFreeCirculation', '0');
            $xml->element('BillingOption', Mage::getStoreConfig('orderpreparation/ups/billing_option'));

            if (Mage::getStoreConfig('orderpreparation/ups/enable_cod')) {
                $xml->push('COD');
                $xml->element('CashierCheckorMoneyOrderOnlyIndicator', '1');
                $xml->element('Amount', $order->getGrandTotal());
                $xml->element('Currency', $order->getOrderCurrencyCode());
                $xml->pop(); //end COD
            }

            $xml->pop(); //end ShipmentInformation

            $xml->pop(); //end OpenShipment
        }


        $xml->pop(); //end OpenShipments

        return $xml->getXml();
    }

    /**
     * Return service type from shipping method associated to order
     * @param <type> $order
     */
    protected function getServiceType($order) {
        $shippingMethod = $order->getshipping_method();
        $shippingMethod = str_replace('ups_', '', $shippingMethod);
        $shippingMethod = strtoupper($shippingMethod);

        //convert numbers to code
        switch($shippingMethod)
        {
            case '01':
                $shippingMethod = '1DA';  //next day air
                break;
            case '02':
                $shippingMethod = '2DA';  //second day air
                break;
            case '03':
                $shippingMethod = 'GND';    //ground
                break;
            case '07':
                $shippingMethod = 'ES';  //worldwide express
                break;
            case '08':
                $shippingMethod = 'EX';  //worldwide expedited
                break;
            case '11':
                $shippingMethod = 'ST';  //stéandard
                break;
            case '12':
                $shippingMethod = '3DS';  //three day select
                break;
            case '13':
                $shippingMethod = '1DP';  //next day air saver
                break;
            case '14':
                $shippingMethod = '1DM';  //next day air saver early AM
                break;
            case '54':
                $shippingMethod = 'EP';  //worldwide express plus
                break;
            case '59':
                $shippingMethod = '2DM';  //second day air AM
                break;
            case '65':
                $shippingMethod = 'SV';  //saver
                break;
        }

        return $shippingMethod;
    }

    /**
     * Method to import trackings
     * @param <type> $t_lines
     */
    public function importTrackingFile($t_lines) {

        $importedTrackingCount = 0;
        $skippedTrackingCount = 0;
        $debug = '';

        //rebuild xml file
        $xmlContent = implode("\n", $t_lines);

        //parse file
        $trackings = array();
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->loadXML($xmlContent);
        $documentElement = $xmlDoc->documentElement;
        foreach ($documentElement->childNodes AS $openShipment) {
            $shipmentId = null;
            $numbers = array();

            //get shipment id
            foreach ($openShipment->childNodes AS $info) {
                if ($info->nodeName == 'ShipTo') {
                    foreach ($info->childNodes AS $data) {
                        if ($data->nodeName == 'Attention')
                            $shipmentId = $data->nodeValue;
                    }
                }
            }

            //get numbers
            foreach ($openShipment->childNodes AS $info) {
                if ($info->nodeName == 'ProcessMessage') {
                    foreach ($info->childNodes AS $data) {
                        if ($data->nodeName == 'TrackingNumbers')
                        {
                            foreach ($data->childNodes AS $num) {
                                $numbers[] = $num->nodeValue;
                            }
                        }
                    }
                }
            }

            $trackings[$shipmentId] = $numbers;

        }

        //insert trackings
        foreach($trackings as $shipmentRef => $numbers)
        {
            foreach($numbers as $number)
            {
                $result = mage::helper('Orderpreparation/Tracking')->addTrackingToShipment($number, $shipmentRef, 'ups', 'ups');
                if ($result)
                    $importedTrackingCount++;
                else
                    $skippedTrackingCount++;
            }
        }

        $msg = mage::helper('Orderpreparation')->__('Tracking import complete : %s tracking imported, %s tracking skipped', $importedTrackingCount, $skippedTrackingCount);
        return $msg;
    }

    /**
     * Check that shipping method is UPS
     * @param <type> $order
     */
    protected function checkShippingMethod($order)
    {
        $shippingMethod = $order->getshipping_method();
        $t = explode('_', $shippingMethod);
        if (isset($t[0]) && ($t[0] == 'ups'))
            return true;
        return false;
    }

}