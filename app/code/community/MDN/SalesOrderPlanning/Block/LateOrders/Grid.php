<?php

class MDN_SalesOrderPlanning_Block_LateOrders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	protected $_parentTemplate = '';
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('SelectedOrdersGrid');
        $this->setDefaultSort('anounced_date');
        $this->setDefaultDir('asc');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
    	$collection = mage::helper('SalesOrderPlanning/LateOrders')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    

    protected function _prepareColumns()
    {

        $this->addColumn('increment_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'type'  => 'number',
            'index' => 'increment_id',
            'renderer' => 'MDN_SalesOrderPlanning_Block_LateOrders_Widget_Grid_Column_Renderer_OrderIncrementId',
        ));
      
        $this->addColumn('shipping_name', array(
            'header'=> Mage::helper('sales')->__('Shipping name'),
            'index' => 'shipping_name'
        ));
        
        $this->addColumn('missing_products', array(
            'header'=> Mage::helper('sales')->__('Missing products'),
            'index' => 'increment_id',
            'renderer' => 'MDN_SalesOrderPlanning_Block_LateOrders_Widget_Grid_Column_Renderer_MissingProducts',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('anounced_date', array(
            'header'=> Mage::helper('sales')->__('Initial delivery date'),
            'type'  => 'date',
            'align' => 'center',
            'index' => 'anounced_date'
        ));

        $this->addColumn('psop_delivery_date', array(
            'header'=> Mage::helper('sales')->__('Estimated delivery date'),
            'type'  => 'date',
            'align' => 'center',
            'index' => 'psop_delivery_date'
        ));

        $this->addColumn('late', array(
            'header'=> Mage::helper('sales')->__('Late'),
            'index' => 'diff',
            'renderer' => 'MDN_SalesOrderPlanning_Block_LateOrders_Widget_Grid_Column_Renderer_Late',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    

}