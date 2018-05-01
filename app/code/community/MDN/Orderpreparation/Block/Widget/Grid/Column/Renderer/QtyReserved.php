<?php

/*
* retourne le contenu d'une commande
*/
class MDN_Orderpreparation_Block_Widget_Grid_Column_Renderer_QtyReserved
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	//recupere les infos
    	$orderItem = $row;
    	$value = $orderItem->getreserved_qty();

    	//recupere le produit
    	$product = mage::getModel('catalog/product')->load($orderItem->getproduct_id());
    	
    	//si le produit ne gere pas les stocks
    	if ($product->getStockItem()->getManageStock())
		{
			if (($orderItem->getqty_ordered() - $orderItem->getRealShippedQty()) == 0)
			{
				$retour = $this->__('Shipped');
			}
			else 
			{
		    	//si la qté est suffisante pour réserver ou déja réservé
		    	if (($product->CanReserveQty($orderItem->getqty_ordered())) || ($value == $orderItem->getqty_ordered()))
		    	{
			    	if ($value > 0)
			    		$checked = " checked ";
			    	else 
				    	$checked = "";
			    	$retour = "<input type=\"checkbox\" name=\"qty_reserved_".$orderItem->getId()."\" values=\"1\" $checked>";
		    	}
		    	else 
		    		$retour = "<font color=\"red\">".$this->__('Stock Insufficient')."</font>";  
			}
		}
		else 
		{
			$retour = "<font color=\"red\">".$this->__('No Stock Management')."</font>";  
		}
		
    	//retourne
        return $retour;
    }
}