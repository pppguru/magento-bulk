<?php
class Extendware_EWCore_Block_Adminhtml_System_Log_Edit extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Grid_Container
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_removeButton('add');
		$this->_removeButton('delete');
		$this->_removeButton('saveandreload');
		
		$this->_addButton('back', array(
            'label'     => $this->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/') .'\')',
            'class'     => 'back',
        ), -1);
        
		$this->_addButton('delete', array(
            'label'     => $this->__('Delete'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/delete', array('_current' => true)) .'\')',
            'class'     => 'delete',
        ), -1);
        
        $this->_addButton('download', array(
			'label' => $this->__('Download'), 
			'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/download', array('_current' => true)) .'\')',
		));
	}
	
	public function getLogFile()
    {
    	return Mage::registry('ew:current_log_file');
    }
    
	public function getHeaderText()
    {
        return $this->__('Log: %s', $this->getLogFile()->getName());
    }
}
