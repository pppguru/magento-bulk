<?php

class MDN_Shipworks_Helper_Action_UpdateOrder extends Mage_Core_Helper_Abstract {

    /**
     * 
     * @return type
     */
    public function process($data) {
        // gather paramtetes
        if (!isset($data['order']) || !isset($data['command']) || !isset($data['comments'])) {
            Mage::helper('Shipworks/Xml')->outputError(40, "Not all parameters supplied.");
            return;
        }

        // newer version of ShipWorks, pull the entity id
        $shipmentId = (int) $data['order'];
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);

        $command = (string) $data['command'];
        $comments = $data['comments'];
        $tracking = $data['tracking'];
        $carrierData = $data['carrier'];

        $this->applyCommand($shipment, $command, $comments, $carrierData, $tracking);
    }

    /**
     * 
     * @param type $order
     * @param type $command
     * @param type $comments
     * @param type $carrierData
     * @param type $tracking
     */
    protected function applyCommand($shipment, $command, $comments, $carrierData, $tracking) {
        try {

            switch (strtolower($command)) {
                case "complete":
                    $this->CompleteShipment($shipment, $comments, $carrierData, $tracking);
                    
                    Mage::helper('Shipworks/Xml')->writeStartTag("Debug");
                    Mage::helper('Shipworks/Xml')->writeElement("OrderStatus", $shipment->getOrder()->getStatus());
                    Mage::helper('Shipworks/Xml')->writeCloseTag("Debug");
                    
                    break;
                case "cancel":
                    Mage::helper('Shipworks/Xml')->outputError(80, "Cancel is not available.");
                    break;
                case "hold":
                    Mage::helper('Shipworks/Xml')->outputError(80, "Hold is not available.");
                    break;
                default:
                    Mage::helper('Shipworks/Xml')->outputError(80, "Unknown order command '$command'.");
                    break;
            }
        } catch (Exception $ex) {
            Mage::helper('Shipworks/Xml')->outputError(90, "Error Executing Command. " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param type $order
     * @param type $comments
     * @param type $carrierData
     * @param type $tracking
     */
    protected function CompleteShipment($shipment, $comments, $carrierData, $tracking) {

        // add tracking info if it was supplied
        if (strlen($tracking) > 0) {
            $track = Mage::getModel('sales/order_shipment_track')->setNumber($tracking);

            # carrier data is of the format code|title
            $carrierData = preg_split("[\|]", $carrierData);
            $track->setCarrierCode($carrierData[0]);
            $track->setTitle($carrierData[1]);

            $shipment->addTrack($track);
        }

        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();
    }

}