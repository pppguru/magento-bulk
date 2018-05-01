<?php

class MDN_Scanner_Block_Menu extends Mage_Adminhtml_Block_Widget_Form
{
	private $_buttons = array(); 

        public function getMenuUrl()
        {
            return $this->getUrl('adminhtml/Scanner_index/index');
        }

	/**
	 * Return menu buttons
	 *
	 */
	public function getButtons()
	{
		return $this->_buttons;
	}

	/**
	 * Add button
	 *
	 * @param unknown_type $label
	 * @param unknown_type $url
	 */
	public function addButton($label, $url, $imgUrl)
	{
		$this->_buttons[] = array('label' => $this->__($label),
									'img' => $this->getSkinUrl($imgUrl),
									'url' => $this->getUrl($url));
		
	}
	
	public function getLogoutUrl()
	{
		return $this->getUrl('adminhtml/index/logout');
	}
	
	public function getSubmitUrl()
	{
		return $this->getUrl('adminhtml/Scanner_Inventory/processSearch');
	}
	
}