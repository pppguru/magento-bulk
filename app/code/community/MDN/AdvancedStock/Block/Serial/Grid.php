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
class MDN_AdvancedStock_Block_Serial_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Enter description here...
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('SerialsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('AdvancedStock/ProductSerial')
                ->getCollection()
                ->join('catalog/product', 'pps_product_id=`catalog/product`.entity_id')
                ->join('AdvancedStock/CatalogProductVarchar', '`catalog/product`.entity_id=`AdvancedStock/CatalogProductVarchar`.entity_id and store_id = 0 and attribute_id = ' . mage::getModel('AdvancedStock/Constant')->GetProductNameAttributeId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareColumns() {
        $this->addColumn('sku', array(
            'header' => Mage::helper('purchase')->__('Sku'),
            'index' => 'sku',
            'sortable' => true
        ));

        $this->addColumn('value', array(
            'header' => Mage::helper('purchase')->__('Product'),
            'index' => 'value',
            'sortable' => true
        ));

        $this->addColumn('pps_serial', array(
            'header' => Mage::helper('purchase')->__('Serial'),
            'index' => 'pps_serial',
            'align' => 'center',
            'sortable' => true
        ));

        $this->addColumn('pps_purchase_order', array(
            'header' => Mage::helper('purchase')->__('Purchase Order'),
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_PurchaseOrder',
            'sortable' => false,
            'filter' => false
        ));

        $this->addColumn('pps_sales_order', array(
            'header' => Mage::helper('purchase')->__('Sales order'),
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_SalesOrder',
            'sortable' => false,
            'filter' => false
        ));
        
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/serials_grid/delete'))
        {
            $this->addColumn('pps_id_delete', array(
                'header' => Mage::helper('purchase')->__('Delete'),
                'index' => 'pps_id',
                'align' => 'center',
                'renderer' => 'MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_DeleteSerial',
                'filter' => false,
                'sortable' => false
            ));
        }
        
        return parent::_prepareColumns();
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    public function getRowUrl($row) {
        //nothing
    }

}
