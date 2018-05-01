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
/*
* retourne les éléments à envoyer pour une commande sélectionnée pour la préparation de commandes
*/
class MDN_Purchase_Block_Widget_Column_Renderer_OrderItemQty
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	//retrieve information
    	$productId =  $this->getColumn()->getproduct_id();
    	$collection = mage::getModel('sales/order_item')
    						->getCollection()
    						->addFieldToFilter('order_id', $row->getId())
    						->addFieldToFilter('product_id', $productId);
    	
    	//return value
    	$retour = 0;
    	switch ($this->getColumn()->getfield_name()) {
    		case 'ordered_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += (int)$item->getqty_ordered();
		    	}
    			break;
    		case 'shipped_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += (int)$item->getRealShippedQty();
		    	}
    			break;
    		case 'remaining_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += $item->getRemainToShipQty();
		    	}
    			break;
    		case 'reserved_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += (int)$item->getreserved_qty();
		    	}
    			break;
    	}
    	
    	if ($retour == '')
			$retour = '0';
			
		return $retour;
    }
    
}