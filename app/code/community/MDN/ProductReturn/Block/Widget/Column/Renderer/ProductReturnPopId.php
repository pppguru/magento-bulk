<?php

/*
 * Retourne le nom d'un fournisseur
 */
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnPopId extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //get purchase price
        $purchaseOrderProduct = Mage::getModel('Purchase/OrderProduct')->load($row->getrsrp_pop_id());
        $purchaseOrder        = $purchaseOrderProduct->getPurchaseOrder();
        $poId                 = $purchaseOrder->getpo_num();
        $html                 = '<a href="' . $this->getUrl('Purchase/Orders/Edit', array('po_num' => $poId)) . '">' . $poId . '</a>';

        return $html;
    }
}