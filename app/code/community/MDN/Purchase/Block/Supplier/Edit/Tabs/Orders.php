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
class MDN_Purchase_Block_Supplier_Edit_Tabs_Orders extends Mage_Adminhtml_Block_Widget_Grid
{
	private $_supplier_id;
		
	/**
	 * Definit le fournisseur
	 *
	 * @param unknown_type $value
	 */
	public function setSupplierId($value)
	{
		$this->_supplier_id = $value;
		return $this;
	}
		
	/**
	 * Retourne le fournisseur
	 *
	 * @param unknown_type $value
	 */
	public function getSupplierId()
	{
		return $this->_supplier_id;
	}
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('AssociatedOrdersGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('supplier_associated_orders');
        $this->setDefaultSort('po_date', 'DESC');
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		//charge les mouvements de stock
		$collection = mage::getModel('Purchase/Order')
			->getCollection()
			->addFieldToFilter('po_sup_num', $this->_supplier_id)
			->join('Purchase/Supplier','po_sup_num=sup_id');

                 
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * Columns grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
                               
        $this->addColumn('po_order_id', array(
            'header'=> Mage::helper('purchase')->__('Ref'),
            'index' => 'po_order_id',
        ));
                   
        $this->addColumn('po_date', array(
            'header'=> Mage::helper('purchase')->__('Date'),
            'index' => 'po_date',
            'type'	=> 'date'
        ));
                                    
        $this->addColumn('po_supply_date', array(
            'header'=> Mage::helper('purchase')->__('Delivery Date'),
            'index' => 'po_supply_date',
            'type'	=> 'date'
        ));

        //useless
        /*$this->addColumn('Supplier', array(
            'header'=> Mage::helper('purchase')->__('Supplier'),
            'index' => 'sup_name',
        ));*/

        $this->addColumn('po_target_warehouse', array(
            'header'=> Mage::helper('purchase')->__('Target warehouse'),
            'index' => 'po_target_warehouse',
            'type' => 'options',
            'options' => mage::getModel('AdvancedStock/Warehouse')->getListAsArray(),
            'align'	=> 'right'
        ));
                                      
        $this->addColumn('po_status', array(
            'header'=> Mage::helper('purchase')->__('Status'),
            'index' => 'po_status',
            'type' => 'options',
            'options' => mage::getModel('Purchase/Order')->getStatuses(),
            'align'	=> 'right'
        ));
        

        $this->addColumn('Amount', array(
            'header'=> Mage::helper('purchase')->__('Amount'),
            'index' => 'amount',
            'renderer'  => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderAmount',
            'align' => 'right',
            'filter'    => false,
            'sortable'  => false

        ));

        $this->addColumn('po_delivery_percent', array(
            'header'=> Mage::helper('purchase')->__('Delivery %'),
            'index' => 'po_delivery_percent',
            'align' => 'center',
            'type'	=> 'number'
        ));
                                                                  
        $this->addColumn('po_paid', array(
            'header'=> Mage::helper('purchase')->__('Paid'),
            'index' => 'po_paid',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return $this->getUrl('*/*/AssociatedOrdersGrid', array('_current'=>true, 'sup_id' => $this->_supplier_id));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    

    /**
     * Define url for each line to edit the order
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $row->getpo_num()));
    }

}
