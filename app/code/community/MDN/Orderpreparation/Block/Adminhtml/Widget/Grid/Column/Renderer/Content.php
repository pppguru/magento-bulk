<?php

/*
* retourne le contenu d'une commande
*/
class MDN_Orderpreparation_Block_Adminhtml_Widget_Grid_Column_Renderer_Content
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$retour = $row->getopp_remain_to_ship();
        return $retour;
    }
}