<?php

class MDN_AdvancedStock_Block_Inventory_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('InventoryGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('AdvancedStock')->__('No Items'));
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('ei_id', 'DESC');
    }

    protected function _prepareCollection()
    {	    		
        $collection = Mage::getModel('AdvancedStock/Inventory')
        	->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    
    protected function _prepareColumns()
    {
    	$this->addColumn('ei_id', array(
            'header'=> Mage::helper('AdvancedStock')->__('#'),
            'index' => 'ei_id',
            'width' => '50px'
        ));

    	$this->addColumn('ei_date', array(
            'header'=> Mage::helper('AdvancedStock')->__('Date'),
            'index' => 'ei_date',
            'type' => 'date'
        ));

    	$this->addColumn('ei_name', array(
            'header'=> Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'ei_name'
        ));

        $this->addColumn('ei_stock_take_method_code', array(
                'header'=> Mage::helper('AdvancedStock')->__('Method'),
                'index' => 'ei_stock_take_method_code'
            ));

    	$this->addColumn('ei_status', array(
            'header'=> Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'ei_status',
            'type' => 'options',
            'width' => '150px',
            'options'	=> mage::getModel('AdvancedStock/Inventory')->getStatuses(),
        ));
        
    	$this->addColumn('ei_warehouse_id', array(
            'header'=> Mage::helper('AdvancedStock')->__('Warehouse'),
            'index' => 'ei_warehouse_id',
            'type' => 'options',
            'options'	=> mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
        ));

        $this->addColumn('ei_comments', array(
            'header'=> Mage::helper('AdvancedStock')->__('Comments'),
            'index' => 'ei_comments'
        ));


        
        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/AdvancedStock_Inventory/Edit', array('ei_id' => $row->getId()));
    }

    public function getNewUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Inventory/Edit');
    }

}
