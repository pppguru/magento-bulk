<?php

/*
* retourne le contenu d'une commande
*/
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnProducts
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $retour = '';

        //create an array with parents
        $parents  = array();
        $products = $row->getProducts();
        
        foreach ($products as $product) {
            $orderItem = Mage::getModel('sales/order_item')->load($product->getrp_orderitem_id());
            $parentKey = ($orderItem->getparent_item_id() ? $orderItem->getparent_item_id() : 'none');
            if (!isset($parents[$parentKey]))
                $parents[$parentKey] = array();
            $parents[$parentKey][] = $product;
        }

        $retour .= '<table cellspacing="0" class="data">';
        $retour .= '<colgroup><col width="25%"><col></colgroup>';
        foreach ($parents as $parentId => $child) {
            $indent = '';
            if ($parentId != 'none') {
                $parentItem = Mage::getModel('sales/order_item')->load($parentId);
                $retour .= '<tr><td><b>' . $parentItem->getSku() . '</b></td>';
                $retour .= '<td><b>' . $parentItem->getName() . '</b></td></tr>';
                $indent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }

            foreach ($child as $product) {
                if ($product->getrp_qty() > 0) {
                    $productload = mage::getmodel('catalog/product')->load($product->getrp_product_id());
                    $retour .= '<tr><td>' . $indent . $productload->getSku() . '</td>';
                    $retour .= '<td>' . $indent . $product->getrp_qty() . 'x ' . $product->getrp_product_name() . '</td></tr>';
                }
            }

        }
        $retour .= '</table>';

        return $retour;
    }
}