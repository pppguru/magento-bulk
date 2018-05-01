<?php

class MDN_Orderpreparation_Helper_SpecificCarrierTemplates_CustomLabel extends MDN_Orderpreparation_Helper_SpecificCarrierTemplates_Abstract {

    /**
     * Generate XML output
     *
     * @param unknown_type $orderPreparationCollection
     */
    public function createExportFile($orderPreparationCollection) {

        $shipmentIds = array();

        foreach ($orderPreparationCollection as $orderToPrepare) {
            $shipmentIds[] = $orderToPrepare->getshipment_id();
        }

        return Mage::getModel('Orderpreparation/Pdf_CustomLabel')->getPdf($shipmentIds)->render();
    }

    /**
     * Method to import trackings
     * @param <type> $t_lines
     */
    public function importTrackingFile($t_lines) {

        throw new Exception('Not implemented');
    }

}