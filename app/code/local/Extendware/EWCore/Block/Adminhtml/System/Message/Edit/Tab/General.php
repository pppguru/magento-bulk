<?php
class Extendware_EWCore_Block_Adminhtml_System_Message_Edit_Tab_General extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		

        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('General Information'),
        ));
      	
		$fieldset->addField('extension', 'label', array(
        	'name'      => 'extension',
            'label'     => $this->__('Extension'),
        	'value' 	=> $this->getSystemMessage()->getExtension(),
        	'bold' 		=> true,
        ));
        
        $fieldset->addField('category', 'label', array(
        	'name'      => 'category',
            'label'     => $this->__('Category'),
        	'value' 	=> $this->getSystemMessage()->getCategory(),
        	'bold' 		=> true,
        ));
        
        
        $fieldset = $form->addFieldset('message_body', array(
        	'legend' => $this->__('Message'),
        	'class'     => 'fieldset-wide'
        ));

        $fieldset->addField('subject', 'label', array(
        	'name'      => 'subject',
            'label'     => $this->__('Subject'),
        	'value' 	=> $this->getSystemMessage()->getSubject(),
        	'bold' 		=> true,
        ));
        
		$fieldset->addField('body', 'textarea', array(
			'name'      => 'body',
			'value'     => $this->getSystemMessage()->getBody(),
		    'label'     => $this->__('Body'),
			'readonly'  => true,
		    'style'     => 'height:200px; width: 100%',
		));
        
        $form->addValues($this->getAction()->getPersistentData('form_data', true));
        $form->addFieldNameSuffix('general');
		$form->setUseContainer(false);
        $this->setForm($form);
        
		return parent::_prepareForm();
	}
    
	public function getSystemMessage() {
        return Mage::registry('ew:current_system_message');
    }
}