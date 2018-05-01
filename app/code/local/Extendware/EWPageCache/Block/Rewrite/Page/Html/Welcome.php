<?php
class Extendware_EWPageCache_Block_Rewrite_Page_Html_Welcome extends Mage_Page_Block_Html_Welcome
{
	const KEY = 'welcome_message_block';
	
	protected function _toHtml()
    {
        return  Mage::helper('ewpagecache')->getBeginMarker(self::KEY) . parent::_toHtml() . Mage::helper('ewpagecache')->getEndMarker(self::KEY);
    }
}