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
class MDN_AdvancedStock_Block_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('ProductsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('AdvancedStock')->__('No Items'));
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('entity_id', 'DESC');
    }

    protected function _prepareCollection() {

        //Charge la collection
        $collection = Mage::getModel('Catalog/Product')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('cost')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('waiting_for_delivery_qty')
                ->addAttributeToSelect('visibility');

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
            $collection = $collection->addAttributeToSelect($manufacturerCode);
        }

        //If special price is active, change the computing algoritmhs
        $today = date('Y-m-d 00:00:00');

        $SqlConditionForSpecialPrice = "if( ( ({{special_price}} < {{price}}) && ({{special_price}} > 0 ) && ({{special_from_date}}<='" . $today . "') && ( (({{special_to_date}} > 0) && ({{special_to_date}}>='" . $today . "')) || ({{special_to_date}} IS NULL) ))";

        //add margin column (depending of price includes taxes setting)
        if (!mage::getStoreConfig('tax/calculation/price_includes_tax')) {
            $collection->addExpressionAttributeToSelect('margin', $SqlConditionForSpecialPrice . ", round(({{special_price}} - {{cost}}) / {{special_price}} * 100, 2), round(({{price}} - {{cost}}) / {{price}} * 100, 2))", array('special_price', 'special_from_date', 'special_to_date', 'price', 'cost'));

            $collection->addExpressionAttributeToSelect('current_sell_price', $SqlConditionForSpecialPrice . ", {{special_price}} , {{price}})", array('special_price', 'special_from_date', 'special_to_date', 'price'));
        } else {
            $defaultTaxRate = Mage::getStoreConfig('purchase/purchase_product/pricer_default_tax_rate');
            $coef = 1 + ($defaultTaxRate / 100);

            $collection->addExpressionAttributeToSelect('margin', $SqlConditionForSpecialPrice . ", round((({{special_price}} / " . $coef . ") - {{cost}}) / ({{special_price}} / " . $coef . ") * 100, 2), round((({{price}} / " . $coef . ") - {{cost}}) / ({{price}} / " . $coef . ") * 100, 2))", array('special_price', 'special_from_date', 'special_to_date', 'price', 'cost'));

            $collection->addExpressionAttributeToSelect('price_excl_tax', $SqlConditionForSpecialPrice . ",({{special_price}} / " . $coef . "),({{price}} / " . $coef . "))", array('special_price', 'special_from_date', 'special_to_date', 'price'));
        }


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

         $this->addColumn('entity_id', array(
            'header' => Mage::helper('Organizer')->__('ID#'),
            'sort' => true,
            'type' => 'number',
            'index' => 'entity_id'
        ));

        $this->addColumn('organiser', array(
            'header' => Mage::helper('Organizer')->__('Organizer'),
            'renderer' => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'product',
            'filter' => false,
            'sort' => false
        ));

        if (Mage::getStoreConfig('advancedstock/general/display_image_in_product_list')) {
            $this->addColumn('picture', array(
                'header' => Mage::helper('AdvancedStock')->__('Picture'),
                'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_Picture',
                'filter' => false,
                'align' => 'center',
                'sort' => false,
            ));
        }

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
            $this->addColumn($manufacturerCode, array(
                'header' => Mage::helper('purchase')->__('Manufacturer'),
                'index' => $manufacturerCode,
                'type' => 'options',
                'options' => $this->getManufacturersAsArray($manufacturerCode),
            ));
        }

        $this->addColumn('barcode', array(
            'header' => Mage::helper('AdvancedStock')->__('Barcode'),
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_Barcode',
            'filter' => 'AdvancedStock/Product_Widget_Grid_Column_Filter_Barcode',
            'align' => 'center',
            'entity' => 'product',
            'sort' => false,
            'index' => 'entity_id'
        ));


        $this->addColumn('Sku', array(
            'header' => Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'name',
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this);

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/productlist_columns/buying_price')) {
            $this->addColumn('buy_price', array(
                'header' => Mage::helper('AdvancedStock')->__('Buy Price'),
                'index' => 'cost',
                'type' => 'price',
                'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/productlist_columns/sale_price')) {
            if (!mage::getStoreConfig('tax/calculation/price_includes_tax')) {
                $this->addColumn('sell_price', array(
                    'header' => Mage::helper('AdvancedStock')->__('Sell Price'),
                    'index' => 'current_sell_price',
                    'type' => 'price',
                    'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                    'align' => 'center'
                ));
            } else {
                $this->addColumn('sell_price', array(
                    'header' => Mage::helper('AdvancedStock')->__('Sell Price'),
                    'index' => 'price_excl_tax',
                    'type' => 'price',
                    'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                    'align' => 'center'
                ));
            }
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/productlist_columns/margin')) {
            $this->addColumn('margin', array(
                'header' => Mage::helper('AdvancedStock')->__('Margin %'),
                'index' => 'margin',
                'type' => 'number',
                'align' => 'center'
            ));
        }
        $this->addColumn('stock_summary', array(
            'header' => Mage::helper('AdvancedStock')->__('Stock Summary'),
            'index' => 'entity_id',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'filter' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_StockSummary',
            'sortable' => false
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'width' => '80',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
        $this->addColumn('visibility', array(
            'header' => Mage::helper('AdvancedStock')->__('Visibility'),
            'width' => '80',
            'index' => 'visibility',
            'type' => 'options',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray()
        ));

        //raise event to allow other modules to add columns
        Mage::dispatchEvent('advancedstock_product_grid_preparecolumns', array('grid' => $this));

        $this->addExportType('adminhtml/AdvancedStock_Products/exportCsv', Mage::helper('AdvancedStock')->__('CSV'));
        $this->addExportType('adminhtml/AdvancedStock_Products/exportExcel', Mage::helper('AdvancedStock')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        //nothing
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    public function getRowUrl($row) {
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view'))
            return $this->getUrl('adminhtml/AdvancedStock_Products/Edit', array()) . "product_id/" . $row->getId();
    }

    
    /**
     * Manage mass actions
     *
     * @return unknown
     */
    protected function _prepareMassaction()
    {
    	parent::_prepareMassaction();

        //set a warehouse as favorite warehouse
        $this->getMassactionBlock()->addItem('change_warehouse_favorite', array(
                'label' => Mage::helper('AdvancedStock')->__('Define warehouse as favorite'),
                'url' => $this->getUrl('*/*/MassChangeFavoriteWarehouse'),
                'additional' => array(
                    'methods' => array(
                        'name' => 'warehouses',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('AdvancedStock')->__('Warehouse'),
                        'values' => mage::helper('AdvancedStock/Warehouse')->getWarehouses())
                )
            ));

        //raise event to allow other extension to add columns
        Mage::dispatchEvent('advancedstock_product_grid_preparemassaction', array('grid' => $this));

        return $this;
    }


    /**
     * Return manufacturers list as array
     *
     */
    public function getManufacturersAsArray($manufacturerCode) {
        $retour = array();


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
