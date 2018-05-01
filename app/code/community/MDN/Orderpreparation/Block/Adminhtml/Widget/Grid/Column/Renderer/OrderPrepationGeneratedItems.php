<?php

/*
* retourne les éléments à envoyer pour une commande sélectionnée pour la préparation de commandes
*/
class MDN_Orderpreparation_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderPrepationGeneratedItems
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	//recupere les infos
    	$retour = $this->_getValue($row);
    	return $retour;
    }
    
}