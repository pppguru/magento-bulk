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
class MDN_Purchase_Block_Order_Edit_Tabs_SupplyNeeds extends Mage_Adminhtml_Block_Widget_Grid
{
	private $_order = null;
	
	/**
	 * Définit l'order
	 *
	 */
	public function setOrderId($value)
	{
		$this->_order = mage::getModel('Purchase/Order')->load($value);
		return $this;
	}
	
	/**
	 * Retourne la commande
	 *
	 */
	public function getOrder()
	{
		return $this->_order;
	}
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('SupplyNeeds');
		//$this->_parentTemplate = $this->getTemplate();
        //$this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //$this->setVarNameFilter('product_selection');
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
        $collection = Mage::getModel('Purchase/SupplyNeeds')
        	->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * Défini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

	    $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $this->getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));
            
		$this->addColumn('sn_manufacturer_id', array(
            'header'=> Mage::helper('purchase')->__('Manufacturer'),
            'index' => 'sn_manufacturer_id',
            'type' => 'options',
            'options' => $this->getManufacturersAsArray(),
        ));
                   
        $this->addColumn('sn_product_name', array(
            'header'=> Mage::helper('purchase')->__('Product'),
            'index' => 'sn_product_name'
        ));
                                    
        $this->addColumn('sn_status', array(
            'header'=> Mage::helper('purchase')->__('Status'),
            'index' => 'sn_status',
            'align' => 'center',
            'type' => 'options',
            'options' => array(
                'supply_order' => Mage::helper('purchase')->__('Supply for orders'),
                'supply_min_qty' => Mage::helper('purchase')->__('Supply for mini qty'),
                'OK' => Mage::helper('purchase')->__('Ok')
            ),
        ));
                  
        $this->addColumn('sn_details', array(
            'header'=> Mage::helper('purchase')->__('Details'),
            'index' => 'sn_details',
            'renderer'  => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsDetails',
            'align'	=> 'center',
            'filter'    => false,
            'sortable'  => false
        ));
                                      
        $this->addColumn('sn_needed_qty', array(
            'header'=> Mage::helper('purchase')->__('Qty'),
            'index' => 'sn_needed_qty',
            'align'	=> 'center'
        ));
        
        $this->addColumn('sn_deadline', array(
            'header'=> Mage::helper('purchase')->__('Dead Line'),
            'index' => 'sn_deadline',
            'type'	=> 'date',
            'align'	=> 'center'
        ));
        
        $this->addColumn('sn_purchase_deadline', array(
            'header'=> Mage::helper('purchase')->__('Dead Line<br>for Purchase'),
            'index' => 'sn_purchase_deadline',
            'type'	=> 'date',
            'align'	=> 'center'
        ));
        
                          
        $this->addColumn('sn_suppliers_name', array(
            'header'=> Mage::helper('purchase')->__('Suppliers'),
            'index' => 'sn_suppliers_ids',    
            'filter'    => 'Purchase/Widget_Column_Filter_SupplyNeedsSuppliers',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsSuppliers'       
        ));
                     
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/SupplyNeedsGrid', array('_current'=>true, 'po_num' => $this->getOrder()->getId()));
    }

    public function getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', null);
        if (!is_array($products)) {
            $products = array();
        }
        return $products;
    }
    
}
