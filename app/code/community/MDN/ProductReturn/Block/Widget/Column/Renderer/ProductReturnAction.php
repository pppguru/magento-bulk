<?php

/*
* retourne le contenu d'une commande
*/
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnAction
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $retour = 'X';

        switch ($row->getrma_action()) {
            case MDN_ProductReturn_Model_Rma::kActionExchange:
                $retour = $this->__('Product exchange');
                $retour .= '';
                break;
            case MDN_ProductReturn_Model_Rma::kActionProductReturn:
                $retour = $this->__('Product returned');

                break;
            case MDN_ProductReturn_Model_Rma::kActionRefund :
                $retour = $this->__('Refunded');
                break;
        }

        return $retour;
    }
}