<?php

/*
* retourne le contenu d'une commande
*/
class MDN_Orderpreparation_Block_Widget_Grid_Column_Renderer_CreatedItems
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$retour = '';
		
		$orderPreparation = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($row->getId());
		
    	if ($orderPreparation->getId())
    	{
	    	if ($orderPreparation->getinvoice_id())
	    		$retour .= '<font color="green">'.$this->__('Invoice created')."</font><br>";
	    	else 
	    		$retour .= '<font color="red">'.$this->__('Invoice not created')."</font><br>";
	
	    	if ($orderPreparation->getshipment_id())
	    		$retour .= '<font color="green">'.$this->__('Shipment created')."</font><br>";
	    	else 
	    		$retour .= '<font color="red">'.$this->__('Shipment not created')."</font><br>";
    	}	
    		
    	return $retour;
    }
}