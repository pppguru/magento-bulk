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
class MDN_Purchase_Block_Product_Widget_StockDetails_SalesOrder extends Mage_Adminhtml_Block_Widget_Grid {

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
        return $this->_product;
    }

    public function __construct() {
        parent::__construct();
        $this->setId('PendingCustomerOrdersGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('pending_customer_orders');
        $this->setDefaultSort('increment_id', 'DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = mage::helper('AdvancedStock/Product_Base')
                ->GetPendingOrders($this->getProduct()->getId(), false);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('AdvancedStock')->__('Id'),
            'index' => 'increment_id',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'MDN_Purchase_Block_Product_Widget_Column_Renderer_SalesOrderIncrementLink'
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('AdvancedStock')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
            'filter' => false,
            'sortable' => false,
            'format' => $dateFormatIso
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


        $this->addColumn('status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'filter' => false,
            'sortable' => false
        ));

        Mage::dispatchEvent('purchase_stockdetails_pendingsalesorder_preparecolumns', array('grid' => $this));

        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * D�finir l'url pour chaque ligne
     * permet d'acc�der � l'�cran "d'�dition" d'une commande
     */
    public function getRowUrl($row) {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

}
