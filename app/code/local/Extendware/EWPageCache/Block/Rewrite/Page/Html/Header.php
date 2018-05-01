<?php
class Extendware_EWPageCache_Block_Rewrite_Page_Html_Header extends Mage_Page_Block_Html_Header 
{
	const KEY = 'welcome_message';
	
    public function getWelcome()
    {
        return  Mage::helper('ewpagecache')->getBeginMarker(self::KEY) . parent::getWelcome() . Mage::helper('ewpagecache')->getEndMarker(self::KEY);
    }
}