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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Block_Admin_Sales_Order_View_Tab_ProductReturn extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductReturnGrid');
        //$this->setTemplate('ProductReturn/list.phtml');        
        $this->_parentTemplate = $this->getTemplate();
        $this->setTemplate('ProductReturn/list.phtml');
        $this->setEmptyText(Mage::helper('ProductReturn')->__('No Items Found'));
        $this->setFilterVisibility(false);
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {

        $order_id   = $this->getRequest()->getParam('order_id');
        $collection = Mage::getModel('ProductReturn/Rma')->loadByOrder($order_id);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('rma_ref', array(
            'header' => Mage::helper('ProductReturn')->__('Ref'),
            'index'  => 'rma_ref',
            'width'  => '100px'
        ));

        $this->addColumn('rma_created_at', array(
            'header' => Mage::helper('ProductReturn')->__('Date'),
            'index'  => 'rma_created_at',
            'type'   => 'date'
        ));

        $this->addColumn('rma_customer_name', array(
            'header' => Mage::helper('ProductReturn')->__('Customer'),
            'index'  => 'rma_customer_name'
        ));

        $this->addColumn('rma_status', array(
            'header'  => Mage::helper('ProductReturn')->__('Status'),
            'index'   => 'rma_status',
            'type'    => 'options',
            'options' => mage::getModel('ProductReturn/Rma')->getStatuses(),
        ));

        $this->addColumn('rma_products', array(
            'header'   => Mage::helper('ProductReturn')->__('Products'),
            'index'    => 'rma_products',
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnProducts',
            'filter'   => false,
            'sortable' => false
        ));

        $this->addColumn('comments', array(
            'header'   => Mage::helper('ProductReturn')->__('Comments'),
            'renderer' => 'MDN_ProductReturn_Block_Widget_Column_Renderer_Comments'
        ));

        return parent::_prepareColumns();
    }


    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));

        return $this->fetchView($templateName);
    }

    /**
     * D�finir l'url pour chaque ligne
     * permet d'acc�der � l'�cran "d'�dition" d'une commande
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $row->getId()));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('ProductReturn')->__('Product Returns');
    }

    public function getTabTitle()
    {
        return Mage::helper('ProductReturn')->__('Product Returns');
    }

    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/order_view/creatermainorderview');
    }

    public function isHidden()
    {
        return false;
    }

}
