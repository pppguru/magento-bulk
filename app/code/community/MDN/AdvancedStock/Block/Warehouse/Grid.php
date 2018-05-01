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
class MDN_AdvancedStock_Block_Warehouse_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('WarehouseGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setDefaultSort('stock_id', 'asc');
        $this->setUseAjax(true);
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		//charge
        $collection = Mage::getModel('AdvancedStock/Warehouse')
        	->getCollection();
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
        $this->addColumn('stock_code', array(
            'header'=> Mage::helper('AdvancedStock')->__('Code'),
            'index' => 'stock_code'
        ));


        $this->addColumn('stock_name', array(
            'header'=> Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'stock_name'
        ));

        $this->addColumn('stock_address', array(
            'header'=> Mage::helper('AdvancedStock')->__('Address'),
            'index' => 'stock_address'
        ));

        $this->addColumn('stock_disable_supply_needs', array(
            'header' => Mage::helper('sales')->__('Disable supply needs'),
            'index' => 'stock_disable_supply_needs',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        $this->addColumn('value', array(
            'header'=> Mage::helper('AdvancedStock')->__('Stock value'),
            'index' => 'stock_name',
            'filter'    => false,
            'sortable'  => false,
            'align'     => 'right',
            'renderer' => 'MDN_AdvancedStock_Block_Warehouse_Widget_Grid_Column_Renderer_Value',

        ));

        $this->addColumn('assignments', array(
            'header'=> Mage::helper('AdvancedStock')->__('Assignments'),
            'index' => 'stock_name',
            'filter'    => false,
            'sortable'  => false,
            'renderer' => 'MDN_AdvancedStock_Block_Warehouse_Widget_Grid_Column_Renderer_StockAssignments',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/warehouseGrid', array('_current'=>true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    public function getNewUrl()
    {
    	return $this->getUrl('adminhtml/AdvancedStock_Warehouse/New');
    }
        
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/AdvancedStock_Warehouse/Edit', array('stock_id' => $row->getId()));
    }
}
