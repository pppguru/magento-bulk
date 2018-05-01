<?php

class Extendware_EWCore_Block_Adminhtml_System_Report_Edit extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        $this->_removeButton('save');
		$this->_removeButton('saveandreload');
		
        $this->_addButton('back', array(
            'label'     => $this->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/') .'\')',
            'class'     => 'back',
        ), -1);
        
		$this->_addButton('delete', array(
            'label'     => $this->__('Delete'),
            'onclick'   => 'confirmSetLocation(\''. $this->__('Are you sure?') . '\', \''. $this->getUrl('*/*/delete', array('_current' => true)) . '\')',
            'class'     => 'delete',
        ), -1);
        
        $this->_addButton('download', array(
			'label' => $this->__('Download'), 
			'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/download', array('_current' => true)) .'\')',
		));
    }

	public function getReportFile()
    {
    	return Mage::registry('ew:current_report_file');
    }
    
	public function getHeaderText()
    {
        return $this->__('Report: %s', $this->getReportFile()->getName());
    }
}
