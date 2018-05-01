<?php

class Extendware_EWCore_Block_Adminhtml_System_Message_Grid extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setDefaultSort('created_at');
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->_getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('system_message_id', array(
            'header'    => $this->__('ID'),
        	'index'     => 'system_message_id',
            'align'     => 'right',
            'width'     => '50px',
        ));
		
       $this->addColumn('extension', array(
            'header'    => $this->__('Extension'),
            'index'     => 'extension',
        	'default'	=> ' ---- ',
       		'width'		=> '200px',
       		'type' => 'options',
        	'options' => $this->_getCollection()->createOptionModel('extension')->reorder('value', 'asc')->toGridOptionArray(),
        ));
        
        $this->addColumn('category', array(
            'header'    => $this->__('Category'),
            'index'     => 'category',
        	'width'		=> '125px',
        	'type' => 'options',
        	'options' => $this->_getCollection()->createOptionModel('category')->reorder('value', 'asc')->toGridOptionArray(),
        ));
        
        $this->addColumn('subject', array(
            'header'    => $this->__('Subject'),
            'index'     => 'subject',
        	'default'	=> ' ---- '
        ));
        
        $this->addColumn('created_at', array(
            'header'    => $this->__('Created'),
            'index'     => 'created_at',
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
					'caption' => $this->__('View Info'),
					'url' => array('base' => '*/*/edit'), 'field' => 'id'
				),
				array(
					'caption' => $this->__('Delete'),
					'url' => array('base' => '*/*/delete'), 'field' => 'id',
					'confirm' => $this->__('Are you sure?')
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
        $this->setMassactionIdField('system_message_id');
        $this->getMassactionBlock()->setFormFieldName('ids');

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
			$collection = Mage::getModel('ewcore/system_message')->getCollection();
			$this->setCollection($collection);
		}

		return $this->_collection;
	}
}