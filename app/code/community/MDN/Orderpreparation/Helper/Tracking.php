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
class MDN_Orderpreparation_Helper_Tracking extends Mage_Core_Helper_Abstract {

    /**
     * Add tracking number to shipment
     *
     * @param unknown_type $trackingNumber
     * @param unknown_type $shipmentReference
     * @param unknown_type $shippingMethod
     * @return unknown
     */
    public function addTrackingToShipment($trackingNumber, $shipmentReference, $shippingMethod, $trackingLabel = 'Tracking') {
        $retour = false;

        if (!$trackingNumber)
            return false;
        
        $shipmentReference = str_replace("\n", "", $shipmentReference);
        $shipmentReference = str_replace("\r", "", $shipmentReference);
        $shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentReference);

        if ($this->shipmentContainsTracking($shipment, $trackingNumber))
            return false;

        $debug = 'process tracking ' . $trackingNumber . ' for shipment #' . $shipmentReference . "\n";
        if ($shipment->getId()) {
            if (!$this->shipmentContainsTracking($shipment, $trackingNumber)) {
                try {
                    $debug .= 'import tracking=' . $trackingNumber . ' for shipment=' . $shipment->getincrement_id() . "\n";
                    $track = new Mage_Sales_Model_Order_Shipment_Track();
                    $track->setNumber($trackingNumber)
                            ->setCarrierCode($shippingMethod)
                            ->setTitle($trackingLabel);
                    $shipment->addTrack($track)->save();
                    $retour = true;
                } catch (Exception $ex) {
                    $retour = false;
                    $debug .= 'Error  : ' . $ex->getMessage() . "\n";
                }
            } else {
                $retour = false;
                $debug .= 'Tracking already exist' . "\n";
            }
        } else {
            $retour = false;
            $debug .= 'Unable to retrieve shipment' . "\n";
        }

        return $retour;
    }

    /**
     * Check if a tracking number as already been imported
     *
     * @param unknown_type $shipment
     * @param unknown_type $tracking
     */
    private function shipmentContainsTracking($shipment, $tracking) {
        $exist = false;

        if ($shipment->getOrder()) {
            foreach ($shipment->getOrder()->getTracksCollection() as $track) {
                if ($track->gettrack_number() == $tracking)
                    $exist = true;
            }
        }
        return $exist;
    }

}