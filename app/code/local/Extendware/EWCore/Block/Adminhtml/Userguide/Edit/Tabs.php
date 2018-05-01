<?php

class Extendware_EWCore_Block_Adminhtml_Userguide_Edit_Tabs extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setDestElementId('edit_form');
		$this->setTitle($this->__('Extension'));
	}

	protected function _beforeToHtml()
	{
		$cnt = 0;
		$moduleCollection = Mage::getSingleton('ewcore/module')->getSortedCollection();
		foreach ($moduleCollection as $module) {
			$block = $this->getLayout()->createBlock('ewcore/adminhtml_userguide_edit_tab_general');
			$block->setModule($module);

			if ($block->canShow() === false) continue;
    	
	    	$params = array(
				'label' => $module->getFriendlyName(),
			);
	    	if (!$cnt) {
	    		$params['content'] = $block->toHtml();
	    	} else {
	    		$params['url'] = $this->getUrl('*/*/viewGuide', array('id'=>$module->getId()));
            	$params['class'] = 'ajax';
	    	}
			$this->addTab($module->getId(), $params);
			$cnt++;
		}
		return parent::_beforeToHtml();
	}
}
