<?php

class Extendware_EWCore_Block_Adminhtml_System_Log_Grid extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setDefaultSort('name');
    }

    protected function _prepareCollection()
    {
    	$forceShow = @(bool)$_GET['force_show'];
    	$collection = Mage::getModel('ewcore/system_log')->getFileCollection($forceShow);
    	if (Mage::helper('ewcore/environment')->isDemoServer() === true) {
    		if (!$forceShow) {
    			$collection = Mage::getModel('ewcore/system_log_file_collection');
    		}
    	}
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => $this->__('Name'),
            'index'     => 'name',
        	'sortable'	=> false,
        	'filter'	=> false,
        ));
		
        $this->addColumn('relative_path', array(
            'header'    => $this->__('Path'),
            'index'     => 'relative_path',
        	'sortable'	=> false,
        	'filter'	=> false,
        ));
        
        $this->addColumn('size', array(
            'header'    => $this->__('Size'),
            'index'     => 'size',
        	'sortable'	=> false,
        	'filter'	=> false,
        ));
        
        $this->addColumn('updated_at', array(
            'header'    => $this->__('Updated'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
            'width'     => '155px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
        	'sortable'	=> false,
        	'filter'	=> false,
        ));
        
        $this->addColumn('action', array(
			'header' => $this->__('Action'),
			'width' => '100px',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array(
					'caption' => $this->__('View'),
					'url' => array('base' => '*/*/edit'), 'field' => 'id'
				),
				array(
					'caption' => $this->__('Download'),
					'url' => array('base' => '*/*/download'), 'field' => 'id'
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
        $this->setMassactionIdField('file_id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> $this->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => $this->__('Are you sure?')
        ));
        
        return $this;
    }
}