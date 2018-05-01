<?php

/*
* retourne le contenu d'une commande
*/
class MDN_Orderpreparation_Block_Widget_Grid_Column_Renderer_Comments
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$orderItem = $row;
    	$value = $orderItem->getcomments();
    	$retour = "<textarea cols=\"80\" rows=\"4\" name=\"comments_".$orderItem->getId()."\">".$value."</textarea>";
    	
    	//retourne
        return $retour;
    }
}