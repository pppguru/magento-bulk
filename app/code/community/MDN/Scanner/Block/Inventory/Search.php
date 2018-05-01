<?php

class MDN_Scanner_Block_Inventory_Search extends Mage_Adminhtml_Block_Widget_Form
{
	
	public function getSubmitUrl()
	{
		return $this->getUrl('adminhtml/Scanner_Inventory/processSearch');
	}
}