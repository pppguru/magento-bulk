<?php
class Extendware_EWCrawler_Block_Adminhtml_Url_Edit_Tab_General extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {    	
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		
        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('General Information'),
        ));
      	
        $fieldset->addField('status', 'select', array(
        	'name'      => 'status',
			'label'     => $this->__('Status'),
        	'values'	=> $this->getCustomUrl()->getStatusOptionModel()->toFormSelectOptionArray(),
			'value'		=> $this->getCustomUrl()->getStatus() ? $this->getCustomUrl()->getStatus() : 'enabled',
			'note'		=> $this->__('If disabled, then this URL will not fed to the crawler as a custom url to crawl.'),
            'required'  => true,
        ));

                
        $fieldset->addField('protocol', 'select', array(
        	'name'      => 'protocol',
			'label'     => $this->__('Protocol'),
        	'values'	=> $this->getCustomUrl()->getProtocolOptionModel()->toFormSelectOptionArray(),
			'value'		=> $this->getCustomUrl()->getProtocol() ? $this->getCustomUrl()->getProtocol() : 'protocol',
			'note'		=> $this->__('Whether to crawl secure or non-secure pages or both. Usually non-secure (HTTP) is correct.'),
            'required'  => true,
        ));
        
		$fieldset->addField('path', 'text', array(
        	'name'      => 'path',
			'label'     => $this->__('Path'),
			'value'		=> $this->getCustomUrl()->getPath(),
			'note'		=> $this->__('Relative path to the base path of the store. For example, to crawl http://www.example.com/my-special-page.html you would input /my-special-page.html'),
            'required'  => true,
        ));
		
		$fieldset->addField('cookies', 'text', array(
        	'name'      => 'cookies',
			'label'     => $this->__('Cookies'),
			'value'		=> $this->getCustomUrl()->getCookies(),
			'note'		=> $this->__('All keys and values must be raw URL encoded. Usually you would leave this blank. Format: key1=value1&key2=value2'),
        ));
		
		$fieldset = $form->addFieldset('filter', array(
        	'legend' => $this->__('Filter Information'),
        ));
		
		$fieldset->addField('store_ids', 'multiselect', array(
			'label' => $this->__('Store View'), 
			'name' => 'store_ids', 
			'value' => $this->getCustomUrl()->getId() > 0 ? $this->getCustomUrl()->getStoreIds() : Mage::getResourceModel('core/store_collection')->getAllIds(),
			'values' => Mage::getModel('adminhtml/system_store')->getStoreValuesForForm(),
        	'note' => $this->__('The URL will be crawled for the selected stores and the final URL will change based on the base path of the selected stores.'),
		));
		
		$customerGroupCollection = Mage::getResourceModel('customer/group_collection');
        $fieldset->addField('customer_group_ids', 'multiselect', array(
			'label' => $this->__('Customer Groups'), 
			'name' => 'customer_group_ids', 
			'value' => $this->getCustomUrl()->getId() > 0 ? $this->getCustomUrl()->getCustomerGroupIds() : array(0),
			'values' => $customerGroupCollection->toOptionArray(),
        	'note' => $this->__('URL will be crawled for each of the selected customer groups. In most cases you would only select "NOT LOGGED IN" unless you have a good reason to otherwise.'),
		));
        
		$form->addValues($this->getAction()->getPersistentData('form_data', true));
        $form->addFieldNameSuffix('general');
		$form->setUseContainer(false);
        $this->setForm($form);
        
		return parent::_prepareForm();
	}
    
	public function getCustomUrl() {
        return Mage::registry('ew:current_url');
    }
}
