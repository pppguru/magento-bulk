<?php
class Extendware_EWCore_Block_Adminhtml_System_Information_Edit_Tab_General extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		
        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('General Information'),
        	'class' => 'fieldset-wide',
        ));

        $fieldset->addField('magento_version', 'label', array(
        	'name'      => 'magento_version',
            'label'     => $this->__('Magento Version'),
        	'value' 	=> ucfirst(Extendware_EWCore_Model_Module_License_Item::getPlatformEdition()) . ' ' . Mage::getVersion(),
        	'bold' 		=> true
        ));
        
        $fieldset->addField('base_path', 'label', array(
        	'name'      => 'base_path',
            'label'     => $this->__('Base Path'),
        	'value' 	=> BP,
        	'bold' 		=> true
        ));
        
        $fieldset->addField('timestamp', 'label', array(
        	'name'      => 'timestamp',
            'label'     => $this->__('System Timestamp'),
        	'value' 	=> now(),
        	'bold' 		=> true,
        	'note' 		=> $this->__('The timestamp based on the time reported by PHP'),
        ));
        
         $fieldset->addField('magento_timestamp', 'date_label', array(
        	'name'      => 'magento_timestamp',
            'label'     => $this->__('Magento Timestamp'),
        	'value' 	=> now(),
        	'bold' 		=> true,
        	'format'	=> 'y-MM-dd HH:mm:ss',
        	'note' 		=> $this->__('The timestamp based on the timezone settings set in Magento'),
        ));
        
        $fieldset = $form->addFieldset('misc', array(
        	'legend' => $this->__('Miscellaneous Information'),
        	'class' => 'fieldset-wide',
        ));
        
		$fieldset->addField('websites', 'label', array(
        	'name'      => 'websites',
            'label'     => $this->__('Num Websites'),
        	'value' 	=> count(Mage::app()->getWebsites()) . ' ' . sprintf('(%d)', Extendware_EWCore_Model_Module_License_Item::getNumberOfWebsites()),
        	'note'		=> $this->__('The value in parenthesis is how many Web sites you have for licensing purposes'),
        	'bold' 		=> true,
        ));
        
        $fieldset->addField('stores', 'label', array(
        	'name'      => 'stores',
            'label'     => $this->__('Num Stores'),
        	'value' 	=> count(Mage::app()->getGroups()) . ' ' . sprintf('(%d)', Extendware_EWCore_Model_Module_License_Item::getNumberOfStores()),
        	'note'		=> $this->__('The value in parenthesis is how many stores you have for licensing purposes'),
        	'bold' 		=> true
        ));
        
        $fieldset->addField('views', 'label', array(
        	'name'      => 'views',
            'label'     => $this->__('Num Store Views'),
        	'value' 	=> count(Mage::app()->getStores()) . ' (' . count(Mage::app()->getStores()) . ')',
        	'note'		=> $this->__('The value in parenthesis is how many store views you have for licensing purposes'),
        	'bold' 		=> true
        ));
        
        $fieldset->addField('domains', 'label', array(
        	'name'      => 'domains',
            'label'     => $this->__('Num Domains'),
        	'value' 	=> Extendware_EWCore_Model_Module_License_Item::getNumberOfDomains(),
        	'note'		=> $this->__('The number of domains you have for licensing purposes'),
        	'bold' 		=> true
        ));
        
        $fieldset = $form->addFieldset('diagnostic', array(
        	'legend' => $this->__('Diagnostic Information'),
        	'class' => 'fieldset-wide',
        ));
        
        $fieldset->addField('exec_enabled', 'label', array(
        	'name'      => 'exec_enabled',
            'label'     => $this->__('Exec Enabled'),
        	'value' 	=> ($this->isExecEnabled() ? $this->__('Yes') : $this->__('No')),
        	'bold' 		=> true
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
	
	public function isExecEnabled() {
		$enabled = (@exec('echo "TEST5588"') == 'TEST5588');
		return $enabled;
	}
}