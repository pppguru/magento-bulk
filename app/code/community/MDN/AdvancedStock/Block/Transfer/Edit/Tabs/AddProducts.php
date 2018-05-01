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
class MDN_AdvancedStock_Block_Transfer_Edit_Tabs_AddProducts extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * 
     *
     */
    public function getTransfer() {
        return mage::registry('current_transfer');
    }

   

    public function __construct() {
        parent::__construct();
        $this->setId('TransferProductSelection');
        $this->setUseAjax(true);
        $this->setEmptyText($this->__('No items'));
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection() {

        //limit to simple products
        $allowProductTypes = array();
        $allowProductTypes[] = 'simple';

        //get all products from catalog and their names
        $collection = mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('type_id', $allowProductTypes)
            ->addAttributeToSelect('name');

        //limit to the products present in the source warehouse
        $sourceWarehouseId = $this->getTransfer()->getst_source_warehouse();
        if($sourceWarehouseId){
            $collection->joinTable(
                'cataloginventory/stock_item',
                'product_id=entity_id',
                array('product_id'),
                'stock_id = ' . $sourceWarehouseId
            );
        }

        //add the manufacturer code
        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
            $collection = $collection->addAttributeToSelect($manufacturerCode);
        }

        //remove already selected products
        $alreadyAddedProducts = array();
        foreach ($this->getTransfer()->getProducts() as $item) {
            $alreadyAddedProducts[] = $item->getstp_product_id();
        }
        if (count($alreadyAddedProducts) > 0){
            $collection->addFieldToFilter('entity_id', array('nin' => $alreadyAddedProducts));
        }

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
            'product_id_field_name' => 'entity_id',
            'product_name_field_name' => 'name'
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('AdvancedStock')->__('Qty to transfer'),
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_AddQty',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('AdvancedStock')->__('ID'),
            'sortable' => true,
            'width' => '60px',
            'index' => 'entity_id'
        ));

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode){
            $this->addColumn($manufacturerCode, array(
                'header' => Mage::helper('purchase')->__('Manufacturer'),
                'index' => $manufacturerCode,
                'type' => 'options',
                'options' => $this->getManufacturersAsArray($manufacturerCode),
            ));
        }

        $this->addColumn('Sku', array(
            'header' => Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku',
        ));

        $this->addColumn('Name', array(
            'header' => Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'name'
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this);


        $this->addColumn('source_warehouse_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Source warehouse'),
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_WarehouseStockLevel',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'warehouse_id' => $this->getTransfer()->getst_source_warehouse(),
            'product_id_field_name' => 'entity_id'
        ));
        
        $this->addColumn('target_warehouse_level', array(
            'header' => Mage::helper('AdvancedStock')->__('Target warehouse'),
            'renderer' => 'MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_WarehouseStockLevel',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'warehouse_id' => $this->getTransfer()->getst_target_warehouse(),
            'product_id_field_name' => 'entity_id'

        ));
        

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/AddProductsGrid', array('_current' => true, 'st_id' => $this->getTransfer()->getId()));
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
