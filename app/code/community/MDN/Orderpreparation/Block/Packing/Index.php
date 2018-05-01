<?php

class MDN_OrderPreparation_Block_Packing_Index extends Mage_Core_Block_Template {

    private $_orderCollection = null;

    
    public function getOrderInformationUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_Packing/OrderInformation');
    }

    public function getChangeShippingMethodUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_Packing/ChangeShippingMethod');
    }

    public function getCheckedImageUrl() {
        return $this->getSkinUrl('images/scanner/ok.png');
    }

    public function getCommitPackingUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_Packing/Commit').'####';
    }

    public function getTranslateJson() {
        $translations = array(
            'Scan order to Pack' => $this->__('Scan order to Pack'),
            'Please scan products' => $this->__('Please scan products'),
            'An error occured' => $this->__('An error occured'),
            'Unknown barcode ' => $this->__('Unknown barcode '),
            'Product quantity already scanned !' => $this->__('Product quantity already scanned !'),
            ' scanned' => $this->__(' scanned'),
            ' are missing !' => $this->__(' are missing !'),
            'Please scan serial number for ' => $this->__('Please scan serial number for '),
            'Serial number added : ' => $this->__('Serial number added : '),
            ' (press enter to skip)' => $this->__(' (press enter to skip)'),
            'No serial number saved' => $this->__('No serial number saved'),
            'Please confirm the parcel count' => $this->__('Please confirm the parcel count'),
            );
        return Mage::helper('core')->jsonEncode($translations);
    }
    
    /**
     * 
     * @return type
     */
    public function getOrderToConfirm()
    {
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        if ($orderId)
        {
            return $orderToPrepare = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($orderId);
        }
        else
            return null;
    }
    
     /**
     * Return url to download invoice for packed order
     */
    public function getDownloadInvoiceUrl()
    {
        $currentOrderBeingPrepared = $this->getOrderToConfirm();
        $invoiceId = $currentOrderBeingPrepared->getinvoice_id();
        $url = Mage::helper('adminhtml')->getUrl('*/*/index', array('order_id' => $currentOrderBeingPrepared->getorder_id()));
        if(!$invoiceId){
            Mage::getSingleton('adminhtml/session')->addError($this->__('No Invoice file to download'));
        }else{
            $url = Mage::helper('adminhtml')->getUrl('*/*/printInvoice', array('invoice_increment_id' => $invoiceId));
        }
        return $url;
    }

    
    /**
     * Return url to download packing slip for packed order
     */
    public function getDownloadPackingSlipUrl()
    {
        $currentOrderBeingPrepared = $this->getOrderToConfirm();
        $shipmentId = $currentOrderBeingPrepared->getshipment_id();
        $url = Mage::helper('adminhtml')->getUrl('*/*/index', array('order_id' => $currentOrderBeingPrepared->getorder_id()));
        if(!$shipmentId){
            Mage::getSingleton('adminhtml/session')->addError($this->__('No Shipment file to download'));
        }else{
            $url = Mage::helper('adminhtml')->getUrl('*/*/printShipment', array('shipment_increment_id' => $shipmentId));
        }
        return $url;
    }
    
    /**
     * Return url to download shipping software file for the current order
     */
    public function getDownloadShippingLabelFileUrl()
    {
        $currentOrderBeingPrepared = $this->getOrderToConfirm();
        return Mage::helper('adminhtml')->getUrl('*/*/downloadShippingLabelFile', array('order_id' => $currentOrderBeingPrepared->getorder_id()));
    }
    
    /**
     * Return 1 if system must ask for serial after barcode is scanned
     * @return type
     */
    public function allowScanSerial()
    {
        return (int) Mage::getStoreConfig('orderpreparation/packing/scan_serials');
    }
    
    /**
     * Return 1 if the user must process groups one by one
     */
    public function displayOnlyCurrentGroup()
    {
        return (int) Mage::getStoreConfig('orderpreparation/packing/display_current_group_only');
    }

    /**
     * Return current order
     *
     * @return unknown
     */
    public function getCurrentOrder() {
        return $this->_currentOrder;
    }

    /**
     * Set current order
     *
     */
    public function setCurrentOrder($order) {
        $this->_currentOrder = $order;
    }

    /**
     * return order to prepare collection filtered with the current user and the current warehouse
     *
     * @return unknown
     */
    private function getOrderCollection() {
        if ($this->_orderCollection == null)
            $this->_orderCollection = mage::helper('Orderpreparation/OnePagePreparation')->getOrderList('*');
        return $this->_orderCollection;
    }

    /**
     * return order list as combo
     *
     * @param unknown_type $name
     * @param unknown_type $onchange
     * @return unknown
     */
    public function getOrderListAsCombo($name, $onchange) {
        $retour = '<select name="' . $name . '" id="' . $name . '" onchange="' . $onchange . '">';
        $retour .= '<option value="" >'.$this->__('Please select an order or scan the order barcode').'</option>';
        foreach ($this->getOrderCollection() as $item) {
            $selected = '';
            $currentOrder = $this->getCurrentOrder();
            if (!empty($currentOrder) && ($currentOrder->getId() == $item->getorder_id()))
                $selected = ' selected ';
            $value = $item->getincrement_id();
            $comments = '';
            if ($item->getinvoice_id() != null && $item->getinvoice_id() != '')
                $comments = ' '.$this->__('Invoiced');
            if ($item->getshipment_id() != null && $item->getshipment_id() != '')
                $comments .= ' & '.$this->__('Shipped');
            if ($comments != '')
                $comments = ' - ' . $comments;
            $retour .= '<option value="' . $value . '" ' . $selected . '>' . $this->__('#') . $item->getincrement_id() . ' - ' . $item->getshipping_name() . $comments .'</option>';
        }
        $retour .= '</select>';
        return $retour;
    }

    /**
     * Return order id to display if forced
     * @return bool
     */
    public function getForcedOrderId()
    {
        if (Mage::app()->getRequest()->getParam('force_order'))
        {
            $orderId =  Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            return $order->getIncrementId();
        }
        return false;
    }


}
