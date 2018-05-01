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
class MDN_AdvancedStock_Block_Warehouse_Edit_Tabs_Products extends Mage_Adminhtml_Block_Widget_Grid
{
	private $_warehouseId = null;
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('WarehouseProducts');
        $this->setUseAjax(true);
        $this->setEmptyText($this->__('No items'));
        $this->_parentTemplate = $this->getTemplate();
    }

    protected function _prepareCollection()
    {		            
    	$warehouse = mage::getModel('AdvancedStock/Warehouse')->load($this->getWarehouseId());
		$collection = $warehouse->getStocks();
        $collection->addFieldToFilter('stock_qty', array('gt' => 0));
        $collection->addAttributeToSelect('cost');
        //$collection->addExpressionAttributeToSelect('row_total', 'SUM(at_cost.value * at_stock_qty.qty)', array('cost'));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    
   /**
     * Defini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

            
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('sku'),
            'index'     => 'sku'
        ));
                 
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Product'),
            'index'     => 'name'
        ));
               
        $this->addColumn('stock_qty', array(
            'header'    => Mage::helper('catalog')->__('Qty'),
            'index'     => 'stock_qty',
            'type' => 'number'
        ));
        
        $this->addColumn('stock_location', array(
            'header'    => Mage::helper('AdvancedStock')->__('Shelf<br>Location'),
            'index'     => 'stock_location',
            'align'     => 'center'
        ));

        $this->addColumn('unit_value', array(
            'header'    => Mage::helper('AdvancedStock')->__('Unit value'),
            'index'     => 'cost',
            'type'     => 'price',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('total_value', array(
            'header'=> Mage::helper('AdvancedStock')->__('Total value'),
            'index' => 'total_value',
            'filter'    => false,
            'sortable'  => false,
            'align'     => 'right',
            'renderer' => 'MDN_AdvancedStock_Block_Warehouse_Widget_Grid_Column_Renderer_ProductValue',
        ));

        /*
        $this->addColumn('row_total', array(
            'header'    => Mage::helper('AdvancedStock')->__('Total value'),
            'index'     => 'row_total',
            'type'     => 'price',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        */

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getentity_id',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('View'),
                        'url'     => array(
                            'base'=>'adminhtml/catalog_product/edit'
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ));

        $this->_exportTypes[] = new Varien_Object(
            array(
                'url'   => $this->getUrl('*/*/exportWarehouseProductsCsv', array('_current'=>true, 'warehouse_id' => $this->getWarehouseId())),
                'label' => Mage::helper('AdvancedStock')->__('CSV')
            )
        );
        $this->_exportTypes[] = new Varien_Object(
            array(
                'url'   => $this->getUrl('*/*/exportWarehouseProductsExcel', array('_current'=>true, 'warehouse_id' => $this->getWarehouseId())),
                'label' => Mage::helper('AdvancedStock')->__('Excel')
            )
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/ProductsGrid', array('_current'=>true, 'warehouse_id' => $this->getWarehouseId()));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

    public function getWarehouseId()
    {
    	return $this->_warehouseId;
    }
    public function setWarehouseId($warehouseId)
    {
    	$this->_warehouseId = $warehouseId;
    	return $this;
    }

    public function getStockValue()
    {
        $warehouse = Mage::getModel('AdvancedStock/Warehouse')->load($this->getWarehouseId());
        return $warehouse->getStockValue();
    }

    protected function currencyFormat($value)
    {
        $currency = mage::getModel('directory/currency')->load(Mage::getStoreConfig('currency/options/base'));
        return $currency->formatTxt($value);
    }
}
