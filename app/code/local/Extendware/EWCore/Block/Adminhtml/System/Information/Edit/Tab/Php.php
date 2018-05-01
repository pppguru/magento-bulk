<?php
class Extendware_EWCore_Block_Adminhtml_System_Information_Edit_Tab_Php extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		
        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('PHP Information'),
        	'class' => 'fieldset-wide',
        ));

		$fieldset->addField('php_info', 'direct_row', array(
        	'value' 	=> '<iframe src="' . $this->getPhpInfoUrl() . '" style="width: 99%; text-align: center; border: 0px; height: 400px;"></iframe>',
        ));

        $form->addValues($this->getAction()->getPersistentData('form_data', true));
        $form->addFieldNameSuffix('general');
		$form->setUseContainer(false);
        $this->setForm($form);
        
		return parent::_prepareForm();
	}
	
	public function getPhpInfoUrl() {
		return $this->getUrl('*/*/phpInfo');
	}
}