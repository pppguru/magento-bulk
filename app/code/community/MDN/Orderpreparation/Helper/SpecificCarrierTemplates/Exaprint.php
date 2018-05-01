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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Exaprint extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract {

    const kCrLf = "\r\n";

    /**
     * Generate XML output
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection) {
        $content = '';

        $content = '';
        $header = utf8_decode('$VERSION=110');
        $content .= $header . self::kCrLf;

        //parse orders
        foreach ($orderPreparationCollection as $orderToPrepare) {

            //init objects
            $order = mage::getModel('sales/order')->load($orderToPrepare->getorder_id());
            $shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($orderToPrepare->getshipment_id());
            $address = $this->getAddress($order);

            //check shipping method
            $carrier = $shipment->getOrder()->getShippingCarrier();
            if ($carrier->getCarrierCode() != 'tablerate')
                continue;
            
            $line = '';
            $line .= sprintf('%-35s', $shipment->getincrement_id());      //1
            $line .= sprintf('%-2s', '');             //2
            $line .= sprintf('%08d', $orderToPrepare->getreal_weight() * 100);      //3
            $line .= sprintf('%-15s', '');             //4
            $line .= sprintf('%-35s', utf8_decode($address->getName()));   //5
            $line .= sprintf('%-35s', utf8_decode($address->getStreet(2)));  //6
            $line .= sprintf('%-35s', utf8_decode($address->getStreet(3)));  //7
            $line .= sprintf('%-35s', utf8_decode($address->getStreet(4)));  //8
            $line .= sprintf('%-35s', '');             //9
            $line .= sprintf('%-35s', '');             //10
            $line .= sprintf('%-10s', $address->getPostcode());  //11
            $line .= sprintf('%-35s', utf8_decode($address->getCity()));   //12
            $line .= sprintf('%-10s', '');             //13
            $line .= sprintf('%-35s', utf8_decode($address->getStreet(1)));  //14
            $line .= sprintf('%-10s', '');             //15

            $countryCode = $address->getCountry();
            if ($countryCode == 'FR')
                $countryCode = 'F';

            $line .= sprintf('%-3s', $countryCode);  //16
            $line .= sprintf('%-30s', $address->gettelephone());            //17
            $line .= sprintf('%-15s', '');             //18
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/shipper_name')));     //19
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/address_additional_1')));   //20
            $line .= sprintf('%-35s', '');             //21
            $line .= sprintf('%-35s', '');             //22
            $line .= sprintf('%-35s', '');             //23
            $line .= sprintf('%-35s', '');             //24
            $line .= sprintf('%-10s', mage::getStoreConfig('orderpreparation/exaprint/zip_code'));   //25
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/city')));    //26
            $line .= sprintf('%-10s', '');                 //27
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/street')));    //28
            $line .= sprintf('%-10s', '');                 //29
            $line .= sprintf('%-3s', mage::getStoreConfig('orderpreparation/exaprint/country_code'));  //30
            $line .= sprintf('%-30s', mage::getStoreConfig('orderpreparation/exaprint/telephone'));   //31
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/comment_1')));   //32
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/comment_2')));   //33
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/comment_3')));   //34
            $line .= sprintf('%-35s', utf8_decode(mage::getStoreConfig('orderpreparation/exaprint/comment_4')));   //35
            $line .= sprintf('%-10s', date('d/m/Y'));              //36
            $line .= sprintf('%08d', '');                 //37				
            $line .= sprintf('%-35s', '');                 //38
            $line .= sprintf('%-35s', $shipment->getincrement_id());          //39
            $line .= sprintf('%-29s', '');                 //40
            $line .= sprintf('%09d', '0');                 //41
            $line .= sprintf('%-8s', '');                 //42
            $line .= sprintf('%-35s', '');                 //43
            $line .= sprintf('%-1s', '');                 //44				
            $line .= sprintf('%-35s', '');                 //45
            $line .= sprintf('%-10s', '');                 //46				
            $line .= sprintf('%-80s', mage::getStoreConfig('orderpreparation/exaprint/email'));    //47
            $line .= sprintf('%-35s', mage::getStoreConfig('orderpreparation/exaprint/mobile'));    //48
            $line .= sprintf('%-80s', utf8_decode($shipment->getOrder()->getcustomer_email()));       //49
            $line .= sprintf('%-35s', $address->getmobile());      //50
            $line .= sprintf('%-80s', '');                 //51				

            $content .= $line . self::kCrLf;
        }
        
        return $content;
    }

    /**
     * Method to import trackings
     * @param <type> $t_lines
     */
    public function importTrackingFile($t_lines) {
        $retour = 0;
        $IsFirstLine = true;

        //parcourt les lignes
        foreach ($t_lines as $line) {
            if (!$IsFirstLine) {
                try {
                    //get datas
                    $shipmentId = substr($line, 0, 9);
                    $trackingNumber = substr($line, 96, 11);

                    //Load shipment
                    $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
                    if ($shipment->getOrder()) {
                        //check that tracking doesnt already exists
                        $exist = false;
                        foreach ($shipment->getOrder()->getTracksCollection() as $track) {
                            if (is_object($track->getNumberDetail())) {
                                if ($track->getNumberDetail()->gettracking() == $trackingNumber)
                                    $exist = true;
                            }
                        }
                        
                        //get carrier code
                        $carrier = $shipment->getOrder()->getShippingCarrier();
                        if ($carrier)
                            $carrierCode = $carrier->getCarrierCode();
                        else
                            $carrierCode = '';

                        //Add tracking if not exists
                        if (!$exist) {
                            $track = new Mage_Sales_Model_Order_Shipment_Track();
                            $track->setNumber($trackingNumber)
                                    ->setCarrierCode($carrierCode)
                                    ->setTitle('Suivi');
                            $shipment->addTrack($track)->save();
                            $retour += 1;
                        }
                    }
                } catch (Exception $ex) {
                    Mage::getSingleton('adminhtml/session')->addError(mage::helper('Orderpreparation')->__('Unable to add Tracking Number to Shipment #', $shipmentId));
                }
            }
            else
                $IsFirstLine = false;
        }

        return $retour;
    }

}