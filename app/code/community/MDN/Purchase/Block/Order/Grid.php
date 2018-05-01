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
class MDN_Purchase_Block_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('OrderGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setVarNameFilter('purchase_order');
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
        
        $this->setDefaultSort('po_order_id', 'desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		//charge les devis avec une jointure sur les clients (ma premiere jointure magento :) :) :) :)
        $collection = Mage::getModel('Purchase/Order')
        	->getCollection()
        	->join('Purchase/Supplier',
			           'po_sup_num=sup_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * PO grid columns
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {

        $this->addColumn('organiser', array(
            'header'=> Mage::helper('Organizer')->__('Organizer'),
       		'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'purchase_order',
            'filter' => false,
            'sort' => false
        ));
        
        $this->addColumn('po_order_id', array(
            'header'=> Mage::helper('purchase')->__('Ref'),
            'index' => 'po_order_id',
        ));
                   
        $this->addColumn('po_date', array(
            'header'=> Mage::helper('purchase')->__('Date'),
            'index' => 'po_date',
            'type'	=> 'date'
        ));

        $this->addColumn('po_updated_at', array(
            'header'=> Mage::helper('purchase')->__('Updated at'),
            'index' => 'po_updated_at',
            'type'	=> 'date'
        ));
                                    
        $this->addColumn('po_supply_date', array(
            'header'=> Mage::helper('purchase')->__('Delivery Date'),
            'index' => 'po_supply_date',
            'type'	=> 'date'
        ));
                  
        $this->addColumn('sup_name', array(
            'header'=> Mage::helper('purchase')->__('Supplier'),
            'index' => 'sup_name',
        ));
                                  
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

        $this->addColumn('po_tracking', array(
            'header'=> Mage::helper('purchase')->__('Tracking #'),
            'index' => 'po_tracking',
        ));
        

        $this->addColumn('amount', array(
            'header'=> Mage::helper('purchase')->__('Amount'),
            'index' => 'amount',
            'renderer'  => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderAmount',
            'align' => 'right',
            'filter'    => false,
            'sortable'  => false
        ));

        $this->addColumn('invoices', array(
            'header'=> Mage::helper('purchase')->__('Invoices'),
            'index' => 'po_order_id',
            'renderer'  => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplierInvoice_PoSummary',
            'align' => 'right',
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true
        ));


        $this->addColumn('po_delivery_percent', array(
            'header'=> Mage::helper('purchase')->__('Delivery %'),
            'index' => 'po_delivery_percent',
            'align' => 'center',
            'type'	=> 'number'
        ));
                                                                                       
        $this->addColumn('po_missing_price', array(
            'header'=> Mage::helper('purchase')->__('Missing Prices'),
            'index' => 'po_missing_price',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));
                                                                                                      
        $this->addColumn('po_data_verified', array(
            'header'=> Mage::helper('purchase')->__('Datas verified'),
            'index' => 'po_data_verified',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));



                                                                                                                   
        $this->addColumn('po_is_locked', array(
            'header'=> Mage::helper('purchase')->__('Locked ?'),
            'index' => 'po_is_locked',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return ''; //$this->getUrl('*/*/wishlist', array('_current'=>true));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    

    /**
     * D�finir l'url pour chaque ligne
     * permet d'acc�der � l'�cran "d'�dition" d'une commande
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/Purchase_Orders/Edit', array())."po_num/".$row->getId();
    }
    
    /**
     * Url pour ajouter un Custom Shipping
     *
     */
    public function getNewUrl()
    {
		return $this->getUrl('adminhtml/Purchase_Orders/New', array());
    }
    
}
