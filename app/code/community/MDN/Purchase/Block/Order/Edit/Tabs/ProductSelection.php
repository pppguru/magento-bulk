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
class MDN_Purchase_Block_Order_Edit_Tabs_ProductSelection extends Mage_Adminhtml_Block_Widget_Grid {

    private $_order = null;

    /**
     * Definit l'order
     *
     */
    public function setOrderId($value) {
        $this->_order = mage::getModel('Purchase/Order')->load($value);
        return $this;
    }

    /**
     * Retourne la commande
     *
     */
    public function getOrder() {
        return $this->_order;
    }

    public function __construct() {
        parent::__construct();
        $this->setId('ProductSelection');        
        $this->setUseAjax(true);       	
        $this->setEmptyText($this->__('No items'));
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $allowProductTypes = array();
        $allowProductTypes[] = 'simple';
        $allowProductTypes[] = 'virtual';
        $allowProductTypes[] = 'downloadable';

        $alreadyAddedProducts = array();
        foreach ($this->getOrder()->getProducts() as $item) {
            $alreadyAddedProducts[] = $item->getpop_product_id();
        }
       

        $collection = Mage::getResourceModel('catalog/product_collection')
                        ->addFieldToFilter('type_id', $allowProductTypes);

        $manufacturerCode = mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeCode();
        
        if($manufacturerCode){
          $collection = $collection->addAttributeToSelect($manufacturerCode);
        }

        $collection = $collection->addAttributeToSelect('name')
                        ->addAttributeToSelect('ordered_qty')
                        ->addAttributeToSelect('reserved_qty')                        
                        ->addAttributeToSelect('waiting_for_delivery_qty')
                        ->joinField('stock',
                                'cataloginventory/stock_item',
                                'qty',
                                'product_id=entity_id',
                                '{{table}}.stock_id=1',
                                'left');

        if (mage::helper('purchase')->requireProductSupplierAssociationToAddProductInPo()) {
            $supplierNum = $this->getOrder()->getpo_sup_num();
            $collection->joinField('ref',
                    'Purchase/ProductSupplier',
                    'pps_reference',
                    'pps_product_id=entity_id',
                    'pps_supplier_num=' . $supplierNum,
                    'inner');
        }

        if (count($alreadyAddedProducts) > 0)
            $collection->addFieldToFilter('entity_id', array('nin' => $alreadyAddedProducts));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Defini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->getSelectedProducts(),
            'align' => 'center',
            'index' => 'entity_id'
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('purchase')->__('Qty'),
            'name' => 'qty',
            'type' => 'number',
            'index' => 'qty',
            'width' => '70',
            'editable' => true,
            'edit_only' => false,
            'sortable' => false,
            'filter' => false
        ));

        if (mage::getStoreConfig('purchase/purchase_product_grid/display_product_picture')) {
            $this->addColumn('picture', array(
                'header' => '',
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProductSelection_Image',
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
                    'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_AddProductsPackaging'
                ));
        }

        $this->addColumn('sn_details', array(
            'header' => Mage::helper('purchase')->__('Details'),
            'index' => 'sn_details',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsDetails',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'product_id_field_name' => 'entity_id',
            'product_name_field_name' => 'name'
        ));

        $this->addColumn('barcode', array(
            'header' => Mage::helper('purchase')->__('Barcode'),
            'index' => 'entity_id',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProductSelection_Barcode',
            'filter' => 'AdvancedStock/Product_Widget_Grid_Column_Filter_Barcode',
        ));

        $this->addColumn('Sku', array(
            'header' => Mage::helper('purchase')->__('Sku'),
            'index' => 'sku',
        ));

        $this->addColumn('Name', array(
            'header' => Mage::helper('purchase')->__('Name'),
            'index' => 'name'
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this);

        $this->addColumn('stock_summary', array(
            'header' => Mage::helper('purchase')->__('Stock summary'),
            'index' => 'entity_id',
            'renderer'	=> 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'filter' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_StockSummary',
            'sortable'	=> false
        ));

        if (mage::getStoreConfig('purchase/purchase_product_grid/display_supply_needs')) {
            $this->addColumn('supply_needs', array(
                'header' => Mage::helper('purchase')->__('Supply needs'),
                'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SupplyNeeds',
                'filter' => false,
                'sortable' => false,
                'align' => 'center'
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/sales_history/sales_history')) {
            if (mage::getStoreConfig('purchase/purchase_product_grid/display_sales_history')) {
                $this->addColumn('sales_history', array(
                    'header' => Mage::helper('purchase')->__('Sales history'),
                    'index' => 'entity_id',
                    'filter' => false,
                    'sortable' => false,
                    'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalesHistory'
                ));
            }
        }

        $this->addColumn('waiting_for_delivery_qty', array(
            'header' => Mage::helper('purchase')->__('Waiting<br>for delivery'),
            'index' => 'waiting_for_delivery_qty',
            'type' => 'number'
        ));

        $manufacturerCode = mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
          $this->addColumn($manufacturerCode, array(
              'header' => Mage::helper('purchase')->__('Manufacturer'),
              'index' => $manufacturerCode,
              'type' => 'options',
              'options' => $this->getManufacturersAsArray(),
          ));
        }

        $this->addColumn('Suppliers', array(
            'header' => Mage::helper('purchase')->__('Suppliers'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductSuppliers',
            'filter' => 'Purchase/Widget_Column_Filter_ProductSupplier',
            'index' => 'entity_id'
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/ProductSelectionGrid', array('_current' => true, 'po_num' => $this->getOrder()->getId()));
    }

    public function getSelectedProducts() {
        $products = $this->getRequest()->getPost('products', null);
        if (!is_array($products)) {
            $products = array();
        }
        return $products;
    }

    /**
     * Return manufacturers list as array
     *
     */
    public function getManufacturersAsArray() {
        $retour = array();

        $manufacturerCode = mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeCode();

        if($manufacturerCode){
          //recupere la liste des manufacturers
          $product = Mage::getModel('catalog/product');
          $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                          ->setEntityTypeFilter($product->getResource()->getTypeId())
                          ->addFieldToFilter('attribute_code', $manufacturerCode)
                          ->load(false);

          $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
          $manufacturers = $attribute->getSource()->getAllOptions(false);

          //ajoute au menu
          foreach ($manufacturers as $manufacturer) {
              $retour[$manufacturer['value']] = $manufacturer['label'];
          }
        }

        return $retour;
    }

}
