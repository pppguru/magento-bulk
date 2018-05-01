<?php

class Extendware_EWCore_Block_Adminhtml_Message_Grid extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setDefaultSort('sent_at', 'desc');
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->_getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /*$this->addColumn('message_id', array(
            'header'    => $this->__('ID'),
        	'index'     => 'message_id',
            'align'     => 'right',
            'width'     => '50px',
        ));*/
		
    	$this->addColumn('severity', array(
            'header'    => $this->__('Severity'),
            'index'     => 'severity',
        	'default'	=> ' ---- ',
       		'width'		=> '60px',
       		'type' => 'options',
        	'options' => Mage::getSingleton('ewcore/message')->getSeverityOptionModel()->toGridOptionArray(),
    		'renderer'  => 'ewcore/adminhtml_message_grid_renderer_severity',
    		'sortable' => false
        ));
        
        $this->addColumn('subject', array(
            'header'    => $this->__('Subject'),
            'index'     => 'subject',
        	'default'	=> ' ---- ',
        	'renderer'  => 'ewcore/adminhtml_message_grid_renderer_subject',
        	'sortable' => false
        ));
        
        $this->addColumn('category', array(
            'header'    => $this->__('Category'),
            'index'     => 'category',
        	'default'	=> ' ---- ',
       		'width'		=> '125px',
       		'type' => 'options',
        	'options' => $this->_getCollection()->createOptionModel('category')->reorder('value', 'asc')->toGridOptionArray(),
        	'sortable' => false
        ));
        
        $this->addColumn('state', array(
            'header'    => $this->__('State'),
            'index'     => 'state',
        	'default'	=> ' ---- ',
       		'width'		=> '60px',
       		'type' => 'options',
        	'options' => Mage::getSingleton('ewcore/message')->getStateOptionModel()->toGridOptionArray(),
        	'sortable' => false
        ));
        
        $this->addColumn('sent_at', array(
            'header'    => $this->__('Date Sent'),
            'index'     => 'sent_at',
            'type'      => 'datetime',
            'width'     => '155px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
        	'sortable'	=> true,
        	'filter'	=> false,
        ));
        
        $this->addColumn('action', array(
			'header' => $this->__('Action'),
			'width' => '100px',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array(
					'caption' => $this->__('View Message'),
					'url' => array('base' => '*/*/edit'), 'field' => 'id'
				),
				array(
					'caption' => $this->__('Mark as Read'),
					'url' => array('base' => '*/*/markRead'), 'field' => 'id',
					'confirm' => $this->__('Are you sure you want to mark message as read?')
				),
				array(
					'caption' => $this->__('Delete'),
					'url' => array('base' => '*/*/delete'), 'field' => 'id',
					'confirm' => $this->__('Are you sure you want to delete message?')
				),
				
			),
			'filter' => false,
			'sortable' => false,
			'is_system' => true
		));
		
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction(){
        $this->setMassactionIdField('message_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
		
        $this->getMassactionBlock()->addItem('state', array(
			'label' => $this->__('Change state'),
			'url' => $this->getUrl('*/*/massState'),
			'confirm' => $this->__('Are you sure you want to change read state?'),
			'additional' => array(
                    'visibility' => array(
                         'name' => 'state',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => $this->__('Status'),
                         'values' => Mage::getSingleton('ewcore/message')->getStateOptionModel()->toGridMassActionOptionArray()
                     )
             )
		));
		
        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> $this->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => $this->__('Are you sure?')
        ));
        
        return $this;
    }
    
	protected function _getCollection()
	{
		if ($this->_collection === null) {
			$collection = Mage::getModel('ewcore/message')->getCollection();
			$this->setCollection($collection);
		}

		return $this->_collection;
	}
}