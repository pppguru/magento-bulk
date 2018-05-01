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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_FedexShipManager extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract {

    /**
     * Generate XML output
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection) {
        $content = '';

        //parse orders
        foreach ($orderPreparationCollection as $orderToPrepare) {

            //init objects
            $order = mage::getModel('sales/order')->load($orderToPrepare->getorder_id());
            $shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($orderToPrepare->getshipment_id());
            $address = $this->getAddress($order);

            //set fields
            $lineDatas = array();
            $lineDatas['0'] = '201';                                     //transaction type
            $lineDatas['1'] = $shipment->getincrement_id();              //transaction id

            $lineDatas['4'] = Mage::getStoreConfig('orderpreparation/fedex/shipper_name');              //shipper name
            $lineDatas['5'] = Mage::getStoreConfig('orderpreparation/fedex/shipper_address1');          //shipper address 1
            $lineDatas['6'] = Mage::getStoreConfig('orderpreparation/fedex/shipper_address2');          //shipper address 2
            $lineDatas['7'] = Mage::getStoreConfig('orderpreparation/fedex/shipper_city');              //shipper city
            $lineDatas['8'] = Mage::getStoreConfig('orderpreparation/fedex/shipper_state');             //shipper state
            $lineDatas['9'] = Mage::getStoreConfig('orderpreparation/fedex/shipper_postal_code');       //shipper postal code

            $lineDatas['11'] = $address->getcompany();              //company name
            $lineDatas['12'] = $address->getfirstname().' '.$address->getlastname();              //contact name
            $lineDatas['13'] = $address->getstreet(0);              //address1
            $lineDatas['14'] = $address->getstreet(1);              //address2
            $lineDatas['15'] = $address->getcity();              //city
            $lineDatas['16'] = '';              //state
            $lineDatas['17'] = $address->getpostcode();              //postal code
            $lineDatas['18'] = $address->gettelephone();              //phone number
            $lineDatas['19'] = $order->getcustomer_id();              //customer code
            
            $lineDatas['50'] = $address->getcountry_id();              //country
            $lineDatas['1202'] = $address->getemail();            //customer email

            $lineDatas['50'] = (int)($orderToPrepare->getreal_weight() * 10);              //package weight
            $lineDatas['25'] = $shipment->getincrement_id();              //shipment reference

            $lineDatas['68'] = Mage::getStoreConfig('orderpreparation/fedex/currency');              //currency
            $lineDatas['75'] = Mage::getStoreConfig('orderpreparation/fedex/weight_type');              //Weight type

            $lineDatas['1273'] = Mage::getStoreConfig('orderpreparation/fedex/packaging_type');            //Packaging type
            //$lineDatas['1274'] = Mage::getStoreConfig('orderpreparation/fedex/service_type');            //Service type
            $lineDatas['1274'] = $this->getServiceType($order);                       //Service type
            $lineDatas['116'] = $orderToPrepare->getpackage_count();            //number of package

            $lineDatas['1204'] = $address->getemail();            //ship alert email adderss
            $lineDatas['1206'] = 'Y';                                     //ship alert flag

            $lineDatas['99'] = '';                                       //transaction end


            //build & add line
            $line = '';
            foreach($lineDatas as $key => $value)
            {
                $line .= $key.','.'"'.$value.'"';
            }

            $content .= $line."\r\n";
        }

        return $content;
    }
    
    /**
     * Return service type
     * @param type $order 
     */
    public function getServiceType($order)
    {
        $shippingMethod = $order->getshipping_method();
        $serviceType = Mage::getStoreConfig('orderpreparation/fedex/service_type');
        switch(strtoupper($shippingMethod))
        {
            case 'FEDEX_GROUND_HOME_DELIVERY':
                $serviceType = 86;
                break;
            case 'FEDEX_INTERNATIONAL_ECONOMY':
                $serviceType = 3;
                break;
            case 'FEDEX_FEDEX_EXPRESS_SAVER':
                $serviceType = 70;
                break;
            default:
                $serviceType = Mage::getStoreConfig('orderpreparation/fedex/service_type');
                break;
        }
        return $serviceType;
    }

}