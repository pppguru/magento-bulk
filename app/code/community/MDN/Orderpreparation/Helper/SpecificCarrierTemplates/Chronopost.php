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
class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Chronopost extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract
{

    public function createExportFile($orderPreparationCollection)
    {
        $output = '';
        foreach($orderPreparationCollection as $orderToPrepare)
        {

           $this->_processDownload($this->getPrintShippingLabelUrl($orderToPrepare->getshipment_id()));
            break;

        }

        return $output;
    }



    protected function _processDownload($url) {
        header('Location: '.$url);
        die();
    }


    /**
     * Get adminhtml url for PostNL print shipping label action.
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getPrintShippingLabelUrl($shipmentId)
    {
		return $this->getUrl('adminhtml/chronorelais_sales_impression/print', array('shipment_increment_id' => $shipmentId));
    }



}