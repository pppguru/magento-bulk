<?php

class MDN_Shipworks_AdminController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Reset downloaded in shipworks status for shipment
     */
    public function ResetAction()
    {
       $shipmentIncrementId = $this->getRequest()->getParam('id');
       Mage::helper('Shipworks/Shipments')->flagAsNotSent($shipmentIncrementId);
       
       $this->_redirectReferer();
    }
    
}
