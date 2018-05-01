<?php
class Extendware_EWCore_Block_Adminhtml_System_Report_Edit_Tab_General extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {    	
    	$data = $this->getReportFile()->getParsedData();
    	
    		
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		
        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('General Information'),
        ));
      	
        
        $fieldset->addField('file_path', 'label', array(
        	'name'      => 'file_path',
        	'value'		=> $this->getReportFile()->getRelativePath(),
            'label'     => $this->__('File Path'),
        ));
        
        if (isset($data['message'])) {
	        $fieldset->addField('message', 'label', array(
	        	'name'      => 'message',
	        	'value'		=> $data['message'],
	            'label'     => $this->__('Message'),
	        ));
        }
        
        $fieldset->addField('updated_at', 'date_label', array(
        	'name'      => 'updated_at',
        	'value'		=> $this->getReportFile()->getUpdatedAt(),
            'label'     => $this->__('Updated'),
        ));
		
        
    	$fieldset = $form->addFieldset('context', array(
        	'legend' => $this->__('Context Information'),
        ));

        $data = $this->getReportFile()->getParsedData();
    	if (isset($data['skin'])) {
	        $fieldset->addField('skin', 'label', array(
	        	'name'      => 'skin',
	        	'value'		=> $data['skin'],
	            'label'     => $this->__('Skin'),
	        ));
        }
        
    	if (isset($data['script_name'])) {
	        $fieldset->addField('script_name', 'label', array(
	        	'name'      => 'script_name',
	        	'value'		=> $data['script_name'],
	            'label'     => $this->__('Script Name'),
	        ));
        }
        
    	if (isset($data['url'])) {
	        $fieldset->addField('url', 'label', array(
	        	'name'      => 'url',
	        	'value'		=> $data['url'],
	            'label'     => $this->__('Url'),
	        ));
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
