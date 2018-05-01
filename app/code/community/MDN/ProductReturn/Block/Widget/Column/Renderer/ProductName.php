<?php

/*
* retourne le contenu d'une commande
*/
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductName
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $retour                            = $row->getName();
        $productId                         = $row->getId();
        $configurableAttributesDescription = mage::helper('ProductReturn/Configurable')->getDescription($productId);
        if ($configurableAttributesDescription != '')
            $retour .= '<i>' . $configurableAttributesDescription . '</i>';

        return $retour;
    }
}