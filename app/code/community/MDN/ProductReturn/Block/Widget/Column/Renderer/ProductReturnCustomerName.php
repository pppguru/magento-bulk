<?php

/*
* retourne le contenu d'une commande
*/
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnCustomerName
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $retour = '';
        $retour = $row->getCustomer()->getName();

        return $retour;
    }
}