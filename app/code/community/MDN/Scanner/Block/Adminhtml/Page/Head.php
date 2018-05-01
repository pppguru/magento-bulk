<?php

class MDN_Scanner_Block_Adminhtml_Page_Head extends Mage_Adminhtml_Block_Page_Head
{
	public function removeAllJs($exceptions = array())
	{
		foreach($this->_data['items'] as $key => $item)
		{
			if (preg_match('/^js/', $key))
			{
				if (!in_array($key , $exceptions))
					unset($this->_data['items'][$key]);
			}
		}
	}
	
	public function removeAllCss($exceptions = array())
	{
		foreach($this->_data['items'] as $key => $item)
		{
			if (preg_match('/^skin_css/', $key))
			{
				if (!in_array($key , $exceptions))
					unset($this->_data['items'][$key]);
			}
		}
	}	

}