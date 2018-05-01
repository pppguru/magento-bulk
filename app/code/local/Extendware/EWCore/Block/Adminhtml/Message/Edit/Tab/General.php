<?php
class Extendware_EWCore_Block_Adminhtml_Message_Edit_Tab_General extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		

        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('General Information'),
        ));
      	
		$fieldset->addField('category', 'label', array(
        	'name'      => 'category',
            'label'     => $this->__('Category'),
        	'value' 	=> $this->getMessage()->getCategory(),
        	'bold' 		=> true,
        ));
        
        $fieldset->addField('severity', 'label', array(
        	'name'      => 'severity',
            'label'     => $this->__('Severity'),
        	'value' 	=> $this->getMessage()->getSeverityLabel(),
        	'bold' 		=> true,
        ));
        
        $fieldset->addField('state', 'label', array(
        	'name'      => 'state',
            'label'     => $this->__('State'),
        	'value' 	=> $this->getMessage()->getStateLabel(),
        	'bold' 		=> true,
        ));
        
        $fieldset = $form->addFieldset('message_info', array(
        	'legend' => $this->__('Message'),
        	'class'     => 'fieldset-wide'
        ));

        $fieldset->addField('subject', 'label', array(
        	'name'      => 'subject',
            'label'     => $this->__('Subject'),
        	'value' 	=> $this->getMessage()->getSubject(),
        	'bold' 		=> true,
        ));
        
		$fieldset->addField('body', 'direct_value', array(
			'label'     => $this->__('Body'),
        	'value' 	=> '<div style="max-height: 200px; overflow:auto">' . ($this->getMessage()->getBody() ? $this->getMessage()->getBody() : $this->getMessage()->getSummary()) . '</div>',
        ));
        

        $form->addValues($this->getAction()->getPersistentData('form_data', true));
        $form->addFieldNameSuffix('general');
		$form->setUseContainer(false);
        $this->setForm($form);
        
		return parent::_prepareForm();
	}
    
	public function getMessage() {
        return Mage::registry('ew:current_message');
    }
}