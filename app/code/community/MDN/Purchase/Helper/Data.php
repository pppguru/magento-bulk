<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Update delivery date for one product
     *
     * @param unknown_type $productId
     */
    public function updateProductDeliveryDate($productId) {
        $helper = mage::helper('purchase/Product');
        $helper->updateProductDeliveryDate($productId);
    }

    /**
     * Update waiting for delivery qty for one product
     *
     * @param unknown_type $productId
     */
    public function updateProductWaitingForDeliveryQty($productId) {
        $helper = mage::helper('purchase/Product');
        $helper->updateProductWaitingForDeliveryQty($productId);
    }

    /**
     * Return light picture that displays prototype product window
     *
     * @param unknown_type $productId
     * @param unknown_type $productName
     * @return unknown
     */
    public function getLightForStockDetailsWindow($productId, $productName) {
        $url = mage::helper('adminhtml')->getUrl('adminhtml/Purchase_Products/productStockDetails', array('product_id' => $productId));
        $productName = str_replace("'", "", $productName);
        $productName = str_replace("\"", "", $productName);
        $onclick = "showProductStockSummary('" . $url . "', '" . $productName . "');";

        $retour = '<img onclick="' . $onclick . '" src="' . Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif') . '">';
        return $retour;
    }

    /**
     * Create new order
     */
    public function createNewOrder($supplierId) {

        return Mage::helper('purchase/Order')->createNewOrder($supplierId);

    }

    /**
     * Return true if we cant add a product in a PO if product / supplier association is not set
     */
    public function requireProductSupplierAssociationToAddProductInPo() {
        return (mage::getStoreConfig('purchase/purchase_product/check_product_supplier_association') == 1);
    }

    public function  getLastPoCollection(){
        $collection = mage::getModel('Purchase/Order')
                        ->getCollection()
                        ->setOrder('po_num', 'desc');
        $collection->getSelect()->limit(1000);
        return $collection;
    }

}
