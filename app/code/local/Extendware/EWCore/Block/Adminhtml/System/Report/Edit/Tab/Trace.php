<?php
class Extendware_EWCore_Block_Adminhtml_System_Report_Edit_Tab_Trace extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {    	
    	$data = $this->getReportFile()->getParsedData();
    	
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		
    	$count = 1;
        foreach ($data['trace'] as $trace) {
        	$fieldset = $form->addFieldset('trace_' . $count, array(
        		'legend' => $this->__('Item #%s Information', $count),
        	));
        	
        	if ($trace['file']) {
	        	$fieldset->addField('file_' . $count, 'label', array(
		        	'name'      => 'file_path',
		        	'value'		=> $trace['file'],
		            'label'     => $this->__('File Path'),
		        ));
        	}
        	
        	if ($trace['line']) {
	        	$fieldset->addField('line_' . $count, 'label', array(
		        	'name'      => 'line',
		        	'value'		=> $trace['line'],
		            'label'     => $this->__('Line'),
		        ));
        	}
        	
        	if ($trace['caller']) {
	        	$fieldset->addField('caller_' . $count, 'label', array(
		        	'name'      => 'caller',
		        	'value'		=> $trace['caller'],
		            'label'     => $this->__('Caller'),
		        ));
        	}
        	
        	$count++;
        }
        

        $form->addValues($this->getAction()->getPersistentData('form_data', true));
        $form->addFieldNameSuffix('general');
		$form->setUseContainer(false);
        $this->setForm($form);
        
		return parent::_prepareForm();
	}
    
	public function getReportFile() {
        return Mage::registry('ew:current_report_file');
    }
}
