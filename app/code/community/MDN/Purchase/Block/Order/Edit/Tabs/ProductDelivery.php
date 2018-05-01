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
class MDN_Purchase_Block_Order_Edit_Tabs_ProductDelivery extends Mage_Adminhtml_Block_Widget_Grid {

    private $_order = null;

    /**
     * Set order
     */
    public function setOrder($order) {
        $this->_order = $order;
        return $this;
    }

    /**
     *
     *
     */
    public function getOrder() {
        return $this->_order;
    }

    public function __construct() {
        parent::__construct();
        $this->setId('ProductsDeliveryGrid');
        $this->setUseAjax(true);
        $this->setEmptyText($this->__('No items'));
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $orderId = $this->getOrder()->getid();

        $collection = mage::getResourceModel('Purchase/OrderProduct_collection')
                        ->addFieldToFilter('pop_order_num', $orderId);

        //only products not supplied
        $collection->getSelect()->where('(pop_qty - pop_supplied_qty)  >0');

        //add image picture
        $smallImageTableName = mage::getModel('Purchase/Constant')->getTablePrefix() . 'catalog_product_entity_varchar';
        $collection->getSelect()->joinLeft($smallImageTableName,
                'pop_product_id=`' . $smallImageTableName . '`.entity_id and `' . $smallImageTableName . '`.store_id = 0 and `' . $smallImageTableName . '`.attribute_id = ' . mage::getModel('Purchase/Constant')->GetProductSmallImageAttributeId(),
                array('small_image' => 'value'));

        //join with product
        $productTableName = mage::getModel('Purchase/Constant')->getTablePrefix() . 'catalog_product_entity';
        $collection->getSelect()->joinLeft($productTableName,
                'pop_product_id=`' . $productTableName . '`.entity_id',
                array('sku' => 'sku'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        if (mage::getStoreConfig('purchase/purchase_product_grid/display_product_picture')) {
            $this->addColumn('picture', array(
                'header' => '',
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Image',
                'align' => 'center',
                'filter' => false,
                'sortable' => false
            ));
        }

        $this->addColumn('sku', array(
            'header' => Mage::helper('purchase')->__('Sku'),
            'index' => 'sku'
        ));

        $this->addColumn('pop_product_name', array(
            'header' => Mage::helper('purchase')->__('product'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Name',
            'index' => 'pop_product_name'
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('purchase')->__('Qty'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Qty',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));

        if (Mage::getStoreConfig('purchase/purchase_order/enable_defect_delivery'))
        {
            $this->addColumn('defect_qty', array(
                'header' => Mage::helper('purchase')->__('Defect qty'),
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_DefectQty',
                'align' => 'center',
                'filter' => false,
                'sortable' => false
            ));
        }

        if (mage::helper('purchase/Product_Packaging')->isEnabled()) {
            $this->addColumn('product_packaging', array(
                'header' => Mage::helper('purchase')->__('Packaging'),
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Packaging'
            ));
        }

        $this->addColumn('pop_qty', array(
            'header' => Mage::helper('purchase')->__('Ordered Qty'),
            'index' => 'pop_qty',
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_OrderedQty'
        ));

        $this->addColumn('delivered_qty', array(
            'header' => Mage::helper('purchase')->__('Delivered Qty'),
            'index' => 'pop_supplied_qty',
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_DeliveredQty'
        ));

        $this->addColumn('remaining_qty', array(
            'header' => Mage::helper('purchase')->__('Remaining'),
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_RemainingQty'
        ));

        $this->addColumn('barcode', array(
            'header' => Mage::helper('purchase')->__('Barcode'),
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Barcode'
        ));

        $this->addColumn('serial', array(
            'header' => Mage::helper('purchase')->__('Serials'),
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Serial'
        ));

        $this->addColumn('location', array(
            'header' => Mage::helper('purchase')->__('Location'),
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Location'
        ));

        $this->addColumn('print_barcode', array(
            'header' => Mage::helper('purchase')->__('Print Barcode'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_PrintBarcode',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    /**
     * Url to refresh grid with ajax
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/ProductsDeliveryGrid', array('_current' => true, 'po_num' => $this->getOrder()->getId()));
    }

}
