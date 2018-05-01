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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_RoyalMailForErp extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract
{
	public function getShippingMethodName(){
        return 'RoyalMail';
    }


    public function createExportFile($orderPreparationCollection) {

        $shipmentId = '';

        try {

            $trackingNumbers = array();

            foreach ($orderPreparationCollection as $orderToPrepare) {

                //check shipping method
                $shipmentId = $orderToPrepare->getshipment_id();
                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);

                foreach($shipment->getAllTracks() as $track){
                    $trackingNumbers[] = $track->getNumber();
                }
            }
            
            $output = Mage::getModel('RoyalMailForErp/Pdf_Label')->getForSeveralTrackings($this->getShippingMethodName(),$trackingNumbers);

        }catch(Exception $ex){
            $exceptionLog = 'RoyalMailForErp STOPPED for shipmentId= '.$shipmentId.' '.$ex->getMessage().' '.$ex->getTraceAsString();
            mage::helper('RoyalMailForErp/Log')->log($exceptionLog);
            throw new Exception($exceptionLog);
        }

        return $output;
    }
   
}