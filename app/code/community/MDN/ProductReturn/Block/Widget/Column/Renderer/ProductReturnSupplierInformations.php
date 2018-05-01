<?php

/*
 * Retourne le nom d'un fournisseur
 */
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnSupplierInformations extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        if ($row->getPurchaseOrderItem()) {
            $price = $row->getrsrp_purchase_price();
            if ($price == null) {
                $html = '<i>' . $this->__('Unable to load informations') . '</i>';
            } else {
                //get purchase price
                $pps  = mage::getModel('Purchase/ProductSupplier')->getCollection()->addFieldToFilter('pps_product_id', $row->getrsrp_product_id())->addFieldToFilter('pps_supplier_num', $row->getrsrp_sup_id())->getFirstItem();
                $html = mage::helper('ProductReturn')->__('Supplier reference: ') . ' ' . $pps->getpps_reference() . '<br />' . mage::helper('ProductReturn')->__('Purchase price:') . ' ' . round($price, 2) . ' ' . mage::getStoreConfig('currency/options/base');
            }
        } else
            $html = '<i>' . $this->__('Unknown supplier') . '</i>';

        return $html;
    }

    public function renderExport(Varien_Object $row)
    {

    }
}