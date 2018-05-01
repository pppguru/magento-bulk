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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_UpsBms extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract {

    /**
     * Generate XML output
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection) {


        //browse collection
        foreach ($orderPreparationCollection as $orderToPrepare) {

            //check shipping method
            $order = mage::getModel('sales/order')->load($orderToPrepare->getorder_id());
            if (!$this->checkShippingMethod($order))
                    continue;

            $shipmentId = $orderToPrepare->getshipment_id();
            $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
            $pdf = Mage::getModel('UpsShipment/Pdf_Label')->prepare($shipment)->getPdf();
            return $pdf->render();

        }

    }


    /**
     * Method to import trackings
     * @param <type> $t_lines
     */
    public function importTrackingFile($t_lines) {

        throw new Exception('Not implemented');
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