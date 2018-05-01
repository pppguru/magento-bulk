<?php

/*
 * Retourne le nom d'un fournisseur
 */
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSupplierSku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        if ($row->getrsrp_sup_id() == null) {
            $html = '<i>' . $this->__('Unknown supplier') . '</i>';
        } else {
            if ($row->getPurchaseOrderItem()) {
                $sup_ref = $row->getPurchaseOrderItem()->getpop_supplier_ref();
                $html    = $sup_ref;
            } else
                $html = '<i>' . $this->__('Unknown supplier') . '</i>';
        }

        return $html;
    }
}