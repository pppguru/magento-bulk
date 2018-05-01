<?php

class MDN_OrderPreparation_Block_PickupDeliveryOrders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	protected $_parentTemplate = '';
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('PickupDeliveryOrdersGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
    }

    protected function _prepareCollection()
    {
    	$collection = Mage::helper('Orderpreparation/PickupDeliveryOrders')->getOrders();
        $this->setCollection($collection);
        parent::_prepareCollection();
        //die($this->getCollection()->getSelect());
        return $this;
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('organizer', array(
            'header'=> Mage::helper('Organizer')->__('Organizer'),
            'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'order',
            'filter' => false,
            'sort' => false,
            'entity_id_field' => 'entity_id'
        ));

        $this->addColumn('increment_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'index' => 'increment_id'
        ));
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('AdvancedStock')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
        
        $this->addColumn('billing_name', array(
            'header' => Mage::helper('AdvancedStock')->__('Bill to Name'),
            'index' => 'billing_name',
            'sortable'  => false
        ));
        
        $this->addColumn('shipping_description', array(
            'header' => Mage::helper('AdvancedStock')->__('Shipping method'),
            'index' => 'shipping_description',
            'filter'    => 'MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Filter_ShippingMethod',
            'sortable'  => false,
            'filter_index' => 'shipping_method'
        ));
        
        $this->addColumn('content', array(
            'header' => Mage::helper('AdvancedStock')->__('Content'),
            'renderer'    => 'MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_OrderContent',
            'sortable'  => false,
            'filter' => false
        ));
        
        $this->addColumn('grand_total', array(
            'header' => Mage::helper('AdvancedStock')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
            'filter'    => false,
            'sortable'  => false
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('AdvancedStock')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses()
        ));

        $this->addColumn('generated_items', array(
            'header' => Mage::helper('AdvancedStock')->__('Generated<br>items'),
            'renderer'    => 'MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_GeneratedItems',
            'sortable'  => false,
            'filter' => false
        ));
        
        $this->addColumn('notification', array(
            'header' => Mage::helper('AdvancedStock')->__('Notification'),
            'index' => 'pickup_is_notified',
            'renderer' => 'MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_Notification',
            'filter' => 'MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Filter_YesNoEmpty',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        $this->addColumn('pickup', array(
            'header' => Mage::helper('AdvancedStock')->__('Pickup'),
            'index' => 'pickup_is_picked',
            'renderer' => 'MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_Pickup',
            'filter' => 'MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Filter_YesNoEmpty',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'adminhtml/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
        
        return parent::_prepareColumns();
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');

        $this->getMassactionBlock()->addItem('mass_notify', array(
             'label'=> Mage::helper('Orderpreparation')->__('Mass notify'),
             'url'  => $this->getUrl('*/*/massNotify'),
        ));

        return $this;
    }

}