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
class MDN_Purchase_Block_Order_Edit_Tabs_Deliveries extends Mage_Adminhtml_Block_Widget_Form {

    private $_order = null;
    private $_defaultWarehouse = null;

    /**
     * Constructeur: on charge
     *
     */
    public function __construct() {

        parent::__construct();

        $this->setTemplate('Purchase/Order/Edit/Tab/Deliveries.phtml');
    }

    /**
     * Retourne l'objet
     *
     * @return unknown
     */
    public function getOrder() {
        if ($this->_order == null) {
            $po_num = Mage::app()->getRequest()->getParam('po_num', false);
            $model = Mage::getModel('Purchase/Order');
            $this->_order = $model->load($po_num);
        }
        return $this->_order;
    }

    /**
     * return warehouse list as combo
     *
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function getWarehousesAsCombo($name) {
        $defaultWarehouseId = $this->getOrder()->getpo_target_warehouse();
        return mage::helper('AdvancedStock/Warehouse')->getWarehousesAsCombo($name, $defaultWarehouseId);
    }

    /**
     * Return product location
     *
     * @param unknown_type $productId
     */
    public function getProductLocation($productId) {
        $defaultWarehouse = $this->getDefaultWarehouse();
        return $defaultWarehouse->getProductLocation($productId);
    }

    /**
     * Return default warehouse
     *
     * @return unknown
     */
    protected function getDefaultWarehouse() {
        if ($this->_defaultWarehouse == null) {
            $defaultWarehouseId = mage::getStoreConfig('purchase/purchase_order/default_warehouse_for_delivery');
            $this->_defaultWarehouse = mage::getModel('AdvancedStock/Warehouse')->load($defaultWarehouseId);
        }
        return $this->_defaultWarehouse;
    }

    /**
     * Return url to print barcode sheet
     *
     */
    public function getBarcodeLabelsUrl() {
        return $this->getUrl('adminhtml/Purchase_Orders/PrintBarcodeLabels', array('po_num' => $this->getOrder()->getId()));
    }

    public function getPrintBarcodeUrl()
    {
        return $this->getUrl('adminhtml/Purchase_Orders/PrintLabelForOneProduct', array('po_num' => $this->getOrder()->getId(), 'count' => '[count]', 'product_id' => '[product_id]'));
    }

    /**
     * Return product delivery block
     */
    public function getProductDeliveryBlock() {
        $gridBlock = $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_ProductDelivery');
        $gridBlock->setOrder($this->getOrder());
        return $gridBlock->toHtml();
    }

    /**
     * Return js code to set all products as delivered
     */
    public function getAllProductsDeliveredJs()
    {
        $js = '';
        foreach($this->getOrder()->getProducts() as $product)
        {
            $id = $product->getId();
            $remainingQty = $product->getRemainingQty();
            $js .= "persistantDeliveryGrid.forceChange('delivery_qty_".$id."', '".$remainingQty."');\n";
        }
        return $js;
    }

    /**
     * Return js code to dispaly a popup to tell end user that all products qty are updated
     */
    public function getAfterFillAllConfirmMessage()
    {
        $js = "alert('".Mage::helper('purchase')->__('All product quantities have been updated. Click on save to validate the delivery.')."');\n";
        return $js;
    }



    /**
     * Return deliviers date
     */
    public function getDeliveries()
    {
        return Mage::getSingleton('Purchase/Order_Delivery')->getDeliveriesDate($this->getOrder());
    }

}