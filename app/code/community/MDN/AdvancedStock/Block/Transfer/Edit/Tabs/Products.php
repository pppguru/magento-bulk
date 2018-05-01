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
class MDN_AdvancedStock_Block_Transfer_Edit_Tabs_Products extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     *
     *
     */
    public function getTransfer() {
        return mage::registry('current_transfer');
    }



    public function __construct() {
        parent::__construct();
        $this->setId('TransferProducts');
        $this->setUseAjax(true);
        $this->setEmptyText($this->__('No items'));
    }

    /**
     *
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        $collection = $this->getTransfer()->getProducts();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('sn_details', array(
            'header' => Mage::helper('AdvancedStock')->__('Details'),
            'index' => 'sn_details',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsDetails',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'product_id_field_name' => 'stp_product_id',
            'product_name_field_name' => 'stp_product_name'
        ));

        $this->addColumn('Sku', array(
            'header' => Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'stp_product_sku',
        ));

        $this->addColumn('Name', array(
            'header' => Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'stp_product_name'
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Requested qty'),
            'index' => 'stp_qty_requested',
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_Qty',
            'align' => 'center',
            'sortable' => false,
        ));

        $this->addColumn('source_warehouse_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Source warehouse'),
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_WarehouseStockLevel',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'warehouse_id' => $this->getTransfer()->getst_source_warehouse(),
            'product_id_field_name' => 'stp_product_id'
        ));

        $this->addColumn('target_warehouse_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Target warehouse'),
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_WarehouseStockLevel',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'warehouse_id' => $this->getTransfer()->getst_target_warehouse(),
            'product_id_field_name' => 'stp_product_id',
        ));

        $this->addColumn('remaining_qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Remaining qty'),
            'index' => 'stp_qty_requested',
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_RemainingQty',
            'align' => 'center',
            'sortable' => false,
            'filter' => false,
            'warehouse_id' => $this->getTransfer()->getst_source_warehouse(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/stock_transfer/remove_products'))
        {
            $this->addColumn('remove', array(
                'header' => Mage::helper('AdvancedStock')->__('Remove'),
                'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_Remove',
                'align' => 'center',
                'sortable' => false,
                'filter' => false,
            ));
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/ProductsGrid', array('_current' => true, 'st_id' => $this->getTransfer()->getId()));
    }

}
