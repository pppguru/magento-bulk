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
class MDN_Purchase_Block_Product_Edit_Tabs_AssociatedOrdersGrid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_readOnly = false;

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
        $this->setId('AssociatedOrdersGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_associated_orders');
        $this->setDefaultSort('po_date');
        $this->setDefaultDir('DESC');
    }

    public function setReadOnlyMode()
    {
        $this->_readOnly = true;
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);

    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		$collection = mage::getModel('Purchase/OrderProduct')
			->getCollection()
			->addFieldToFilter('pop_product_id', $this->getProduct()->getId())
			->join('Purchase/Order','po_num=pop_order_num')
			->join('Purchase/Supplier','po_sup_num=sup_id');
                 
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
                               
        $this->addColumn('po_order_id', array(
            'header'=> Mage::helper('purchase')->__('Order'),
            'align' => 'center',
            'index'	=> 'po_order_id',
            'sortable' => false
        ));
        
        $this->addColumn('po_date', array(
            'header'=> Mage::helper('purchase')->__('Date'),
            'index' => 'po_date',
            'type'	=> 'date',
            'sortable' => false
        ));
                      
        $this->addColumn('po_supply_date', array(
            'header'=> Mage::helper('purchase')->__('Delivery Date'),
            'index' => 'po_supply_date',
            'type'	=> 'date',
            'sortable' => false
        ));
		
        $this->addColumn('sup_name', array(
            'header'=> Mage::helper('purchase')->__('Supplier'),
            'index' => 'sup_name',
            'sortable' => false
        ));
                      
        $this->addColumn('pop_qty', array(
            'header'=> Mage::helper('purchase')->__('Qty'),
            'renderer'  => 'MDN_Purchase_Block_Product_Widget_Column_Renderer_OrdererQty',
            'index' => 'pop_id',
            'filter' => false,
            'sortable' => false,
            'align' => 'center'
        ));
                      
        $this->addColumn('pop_supplied_qty', array(
            'header'=> Mage::helper('purchase')->__('Delivered Qty'),
            'type' => 'number',
            'renderer'  => 'MDN_Purchase_Block_Product_Widget_Column_Renderer_DeliveredQty',
            'index' => 'pop_id',
            'filter' => false,
            'sortable' => false,
            'align' => 'center'
        ));
                              
        $this->addColumn('pop_price_ht_base', array(
            'header'=> Mage::helper('purchase')->__('Unit Price'),
            'index' => 'pop_price_ht_base',
            'type'	=> 'price',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'sortable' => false
        ));
                              
        $this->addColumn('pop_extended_costs_base', array(
            'header'=> Mage::helper('purchase')->__('Unit Price + Cost'),
            'index' => 'pop_extended_costs_base',
            'align'	=> 'right',
            'renderer'  => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductUnitPricePlusCost',
            'filterable' => false,
            'sortable' => false
        ));
                                      
                       
        $this->addColumn('Paid', array(
            'header'=> Mage::helper('purchase')->__('Paid'),
            'index' => 'po_paid',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
            'sortable' => false
        ));
        
        $this->addColumn('po_status', array(
            'header'=> Mage::helper('purchase')->__('Status'),
            'index' => 'po_status',
            'type' => 'options',
            'options' => mage::getModel('Purchase/Order')->getStatuses(),
            'align'	=> 'right',
            'sortable' => false
        ));

        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return $this->getUrl('adminhtml/Purchase_Products/AssociatedOrdersGrid', array('_current'=>true, 'product_id' => $this->getProduct()->getId()));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    

    /**
     * Definir l'url pour chaque ligne
     * permet d'acceder a l'ecran "d'edition" d'une commande
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $row->getpo_num()));
    }

}
