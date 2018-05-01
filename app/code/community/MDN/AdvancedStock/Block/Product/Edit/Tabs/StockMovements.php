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
class MDN_AdvancedStock_Block_Product_Edit_Tabs_StockMovements extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Product get/set
	 *
	 * @var unknown_type
	 */
	private $_product = null;
	public function setProduct($Product)
	{
		$this->_product = $Product;
		return $this;
	}
	public function getProduct()
	{
		return $this->_product;
	}

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductStockMovementGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(mage::helper('AdvancedStock')->__('No items'));
        $this->setUseAjax(true);
        $this->setDefaultSort('sm_date', 'desc');
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		//charge
        $collection = mage::getModel('AdvancedStock/StockMovement')
        					->getCollection()
        					->addFieldToFilter('sm_product_id', $this->getProduct()->getId());
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
        
        $this->addColumn('sm_date', array(
            'header'=> Mage::helper('AdvancedStock')->__('Date'),
            'index' => 'sm_date',
            'type'	=> 'datetime'
        ));

        $this->addColumn('sm_source_stock', array(
            'header'=> Mage::helper('AdvancedStock')->__('From warehouse'),
            'index' => 'sm_source_stock',
            'type'	=> 'options',
            'options'	=> mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align'	=> 'center'
        ));

        $this->addColumn('sm_target_stock', array(
            'header'=> Mage::helper('AdvancedStock')->__('To warehouse'),
            'index' => 'sm_target_stock',
            'type'	=> 'options',
            'options'	=> mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align'	=> 'center'
        ));

        $this->addColumn('sm_type', array(
            'header'=> Mage::helper('AdvancedStock')->__('Type'),
            'index' => 'sm_type',
            'type'	=> 'options',
            'options'	=> mage::getModel('AdvancedStock/StockMovement')->GetTypes(),
            'align'	=> 'center'
        ));
        
        $this->addColumn('sm_qty', array(
            'header'=> Mage::helper('AdvancedStock')->__('Qty'),
            'index' => 'sm_qty',
            'align'	=> 'center'
        ));

        $this->addColumn('picto', array(
            'header' => '',
            'index' => 'sm_id',
            'filter' => false,
            'sortable' => false,
            'align' => 'center',
            'is_system' => true,
            'renderer' => 'MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_Picto',
        ));
        
        $this->addColumn('sm_description', array(
            'header'=> Mage::helper('AdvancedStock')->__('Description'),
            'index' => 'sm_description'
        ));


        $this->addColumn('sm_user', array(
            'header' => Mage::helper('AdvancedStock')->__('User'),
            'index' => 'sm_user',
            'align' => 'right',
            'sortable' => true
        ));
        
        if (Mage::getStoreConfig('advancedstock/general/log_adjustment_stock_movement'))
        {
            $this->addColumn('log', array(
                'header' => Mage::helper('AdvancedStock')->__('Log'),
                'index' => 'sm_id',
                'filter' => false,
                'sortable' => false,
                'align' => 'center',
                'renderer' => 'MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_Log',
            ));
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/AdvancedStock_StockMovement/ProductStockMovementGrid', array('product_id' => $this->getProduct()->getId()));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    public function getRowUrl($row)
    {
    	//nothing
    }
}
