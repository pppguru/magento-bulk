<?php

class MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_GeneratedItems extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	/**
	 * Render column
	 *
	 * @param Varien_Object $row
	 * @return unknown
	 */
    public function render(Varien_Object $row)
    {
    	$retour = '<div class="nowrap">';
    	
    	$invoice = $this->getInvoice($row);
    	$shipment = $this->getShipment($row);
    	
    	//display invoice info
    	if (!$invoice)
    		$retour .= '<font color="red">'.$this->__('No invoice').'</font>';
    	else 
    	{
    		$retour .= '<a href="'.$this->getUrl('adminhtml/sales_order_invoice/print', array('invoice_id' => $invoice->getId())).'">'.$this->__('Invoice %s', $invoice->getincrement_id()).'</a>';
    	}
    	
    	$retour .= '<br>';
    		
    	//display shipment info
    	if (!$shipment)
    		$retour .= '<font color="red">'.$this->__('No shipment').'</font>';
    	else 
    	{
    		$retour .= '<a href="'.$this->getUrl('adminhtml/sales_order_shipment/print', array('invoice_id' => $shipment->getId())).'">'.$this->__('Shipment %s', $shipment->getincrement_id()).'</a>';
    	}
    	
    	$retour .= '</div>';
    	
        return $retour;
    }
    
    /**
     * Return first invoice
     *
     * @param unknown_type $order
     * @return unknown
     */
    protected function getInvoice($order)
    {
    	$collection = $order->getInvoiceCollection();	
    	foreach ($collection as $item)
    	{
    		return $item;
    	}
    	
    	return null;
    }
    
    /**
     * Return first shipment
     *
     * @param unknown_type $order
     * @return unknown
     */
    protected function getShipment($order)
    {
    	$collection = $order->getShipmentsCollection();	
    	foreach ($collection as $item)
    	{
    		return $item;
    	}
    	
    	return null;
    }
    
}