<?php

/*
 * Retourne le nom d'un fournisseur
 */
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSupplierName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $retour = '';
        if ($row->getSupplier()) {
            $retour = $row->getSupplier()->getsup_name();
            if ($retour == '') {
                $retour = '<i>' . $this->__('Unknown supplier') . '</i>';
            }
        }

        return $retour;
    }
}