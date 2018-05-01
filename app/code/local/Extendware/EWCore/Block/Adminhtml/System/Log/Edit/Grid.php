<?php

class Extendware_EWCore_Block_Adminhtml_System_Log_Edit_Grid extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setDefaultSort('name');
    }

    protected function _prepareCollection()
    {
        $collection = $this->getLogFile()->getLineCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('priority_name', array(
            'header'    => $this->__('Priority Name'),
            'index'     => 'priority_name',
        	'width'		=> '100px',
        	'sortable'	=> false,
        	'filter'	=> false,
        ));
        
        $this->addColumn('message', array(
            'header'    => $this->__('Message'),
            'index'     => 'message',
        	'renderer'	=> 'Extendware_EWCore_Block_Adminhtml_System_Log_Edit_Grid_Renderer_Message',
        	'sortable'	=> false,
        	'filter' 	=> false,
        ));
        
        $this->addColumn('date', array(
            'header'    => $this->__('date'),
            'index'     => 'date',
            'type'      => 'datetime',
            'width'     => '155px',
            'gmtoffset' => true,
            'default'	=> ' ---- ',
        	'sortable'		=> false,
        	'filter' 	=> false,
        ));
        
        return parent::_prepareColumns();
    }
	
    
    public function getLogFile()
    {
    	return Mage::registry('ew:current_log_file');
    }

}