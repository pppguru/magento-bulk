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
class MDN_AdvancedStock_Block_Product_Edit_Tabs_AllSalesOrder extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Product get/set
     *
     * @var unknown_type
     */
    private $_product = null;

    public function setProduct($Product) {
        $this->_product = $Product;
        return $this;
    }

    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id'));
        }
        return $this->_product;
    }

    /**
     *
     *   SALES HISTORY
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('AllSalesOrderGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('all_orders');
        $this->setRowClickCallback(false);
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $productId = $this->getProduct()->getId();
        $ordersId = $this->getOrderIds($productId);

        //collect orders
        if (mage::helper('AdvancedStock/FlatOrder')->ordersUseEavModel()) {
            $collection = mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $ordersId))
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('state')
                ->addAttributeToSelect('total_paid')
                ->addAttributeToSelect('grand_total')
                ->addAttributeToSelect('payment_validated')
                ->addAttributeToSelect('customer_firstname')
                ->addAttributeToSelect('customer_lastname')
                ->addExpressionAttributeToSelect('billing_name', 'CONCAT({{customer_firstname}}, " ", {{customer_lastname}}, " ")', array('customer_firstname', 'customer_lastname'))
                ->setOrder('entity_id', 'desc');
        } else {
            $collection = mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('main_table.entity_id', array('in' => $ordersId))
                ->join('sales/order_address', '`sales/order_address`.entity_id=billing_address_id', array('billing_name' => "concat(firstname, ' ', lastname)"))
                ->setOrder('entity_id', 'desc');
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     *
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('AdvancedStock')->__('Id'),
            'index' => 'increment_id',
            'filter' => false,
            'width' => '100px',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderLink',
            'sortable' => false
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('AdvancedStock')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('AdvancedStock')->__('Bill to Name'),
            'index' => 'billing_name',
            'filter' => false,
            'sortable' => false
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/sales_history/all_orders/total')) {
            $this->addColumn('grand_total', array(
                'header' => Mage::helper('AdvancedStock')->__('G.T. (Purchased)'),
                'index' => 'grand_total',
                'type' => 'currency',
                'currency' => 'order_currency_code',
                'filter' => false,
                'sortable' => false
            ));
        }

        $this->addColumn('ordered_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Ordered<br>Qty'),
            'index' => 'ordered_qty',
            'product_id' => $this->getProduct()->getId(),
            'field_name' => 'ordered_qty',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderItemQty',
            'align' => 'center',
            'width' => '80px',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('shipped_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Shipped<br>Qty'),
            'index' => 'shipped_qty',
            'product_id' => $this->getProduct()->getId(),
            'field_name' => 'shipped_qty',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderItemQty',
            'align' => 'center',
            'width' => '80px',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('remaining_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Qty to ship'),
            'index' => 'remaining_qty',
            'product_id' => $this->getProduct()->getId(),
            'field_name' => 'remaining_qty',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderItemQty',
            'align' => 'center',
            'width' => '80px',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('reserved_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Reserved<br>Qty'),
            'index' => 'reserved_qty',
            'product_id' => $this->getProduct()->getId(),
            'field_name' => 'reserved_qty',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderItemQty',
            'align' => 'center',
            'width' => '80px',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('qty_invoiced', array(
            'header' => Mage::helper('AdvancedStock')->__('Invoiced<br>Qty'),
            'index' => 'qty_invoiced',
            'product_id' => $this->getProduct()->getId(),
            'field_name' => 'qty_invoiced',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderItemQty',
            'align' => 'center',
            'width' => '80px',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('preparation_warehouse', array(
            'header' => Mage::helper('AdvancedStock')->__('Preparation<br>Warehouse'),
            'index' => 'preparation_warehouse',
            'product_id' => $this->getProduct()->getId(),
            'field_name' => 'preparation_warehouse',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_PreparationWarehouse',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));



        $this->addColumn('valid', array(
            'header' => Mage::helper('AdvancedStock')->__('Valid'),
            'index' => 'is_valid',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('store', array(
            'header' => Mage::helper('AdvancedStock')->__('Store'),
            'type' => 'store',
            'width' => '120px',
            'filter' => false,
            'sortable' => false,
            'is_system' => true
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'filter' => false,
            'sortable' => false
        ));

        //raise event to allow other modules to add columns
        Mage::dispatchEvent('advancedstock_pendingsalesorders_grid_preparecolumns', array('grid' => $this));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/sales_history/all_orders/action')) {
            $this->addColumn('reserve_actions', array(
                'header' => Mage::helper('AdvancedStock')->__('Actions'),
                'index' => 'planning',
                'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_ReserveAction',
                'align' => 'center',
                'product_id' => $this->getProduct()->getId(),
                'filter' => false,
                'sortable' => false,
                'is_system' => true
            ));
        }

        $this->addExportType('adminhtml/AdvancedStock_Products/SaleHistoryExportCsv', Mage::helper('AdvancedStock')->__('CSV'));


        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     *
     */
    public function getRowUrl($row) {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    /**
     * Return all orders ids with this product
     */
    protected function getOrderIds($productId) {
        //todo : do not use sql direct !!!
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'select distinct order_id from ' . $prefix . 'sales_flat_order_item where product_id = ' . $productId;
        $orderIds = mage::getResourceModel('catalog/product')->getReadConnection()->fetchCol($sql);
        return $orderIds;
    }

    /**
     *
     * @return type
     */
    public function getGridUrl() {
        return $this->getUrl('adminhtml/AdvancedStock_Products/AllOrdersGrid', array('_current' => true, 'product_id' => $this->getProduct()->getId()));
    }

}
