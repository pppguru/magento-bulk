<?php

class MDN_ExtensionConflict_Block_DisplayFix extends Mage_Adminhtml_Block_Widget_Form
{
	private $_conflict = null;
	
	public function getConflict()
	{
		if ($this->_conflict == null)
		{
			$ecId = $this->getRequest()->getParam('ec_id');
			$this->_conflict = mage::getModel('ExtensionConflict/ExtensionConflict')->load($ecId);
		}
		return $this->_conflict;
	}
	
	public function getBackUrl()
	{
		return $this->getUrl('adminhtml/ExtensionConflict_Admin/List');
	}
}