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
class MDN_Purchase_Block_Order_Liabilities_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('LiabilitiesGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));

        $this->setDefaultSort('po_due_date', 'desc');
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Purchase/Order')
        	->getCollection()
                ->addFieldToFilter('po_paid', array('neq' => 1))
                ->join('Purchase/Supplier', 'po_sup_num=sup_id', array('sup_name' => 'sup_name', 'po_due_date' => "DATE_ADD(po_invoice_date, INTERVAL sup_payment_delay DAY)"));
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

        $this->addColumn('sup_name', array(
            'header'=> Mage::helper('purchase')->__('Supplier'),
            'index' => 'sup_name',
        ));

        $this->addColumn('po_status', array(
            'header'=> Mage::helper('purchase')->__('Status'),
            'index' => 'po_status',
            'type' => 'options',
            'options' => mage::getModel('Purchase/Order')->getStatuses(),
            'align'	=> 'right'
        ));


        $this->addColumn('amount', array(
            'header'=> Mage::helper('purchase')->__('Amount'),
            'index' => 'amount',
            'renderer'  => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderAmount',
            'align' => 'right',
            'filter'    => false,
            'sortable'  => false

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

        $this->addColumn('po_delivery_percent', array(
            'header'=> Mage::helper('purchase')->__('Delivery %'),
            'index' => 'po_delivery_percent',
            'align' => 'center',
            'type'	=> 'number'
        ));

        $this->addColumn('po_invoice_date', array(
            'header'=> Mage::helper('purchase')->__('Invoice Date'),
            'index' => 'po_invoice_date',
            'align' => 'center',
            'type'	=> 'date'
        ));

        $this->addColumn('po_due_date', array(
            'header'=> Mage::helper('purchase')->__('Due date'),
            'index' => 'po_due_date',
            'align' => 'center',
            'type'	=> 'date'
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));

        return parent::_prepareColumns();
    }

    /**
     *
     * @return <type> 
     */
    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }


    /**
     * 
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $row->getId()));
    }

}
