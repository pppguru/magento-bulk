<?php
class Extendware_EWPageCache_Block_Rewrite_Page_Template_Links extends Mage_Page_Block_Template_Links
{
	public function addLink($label, $url='', $title='', $prepare=false, $urlParams=array(), $position=null, $liParams=null, $aParams=null, $beforeText='', $afterText='')
    {
    	if (Extendware::helper('ewpagecache')) {
	    	if (strpos($url, 'wishlist') !== false) {
	        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_wishlist') . $beforeText;
	        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_wishlist');
	        } elseif (strpos($url, 'checkout/cart') !== false) {
	        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_cart') . $beforeText;
	        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_cart');
	        } elseif (strpos($url, 'account/login') !== false or strpos($url, 'account/logout') !== false) {
	        	$beforeText = Mage::helper('ewpagecache')->getBeginMarker('toplink_login') . $beforeText;
	        	$afterText = $afterText . Mage::helper('ewpagecache')->getEndMarker('toplink_login');
	        }
    	}
        return parent::addLink($label, $url, $title, $prepare, $urlParams, $position, $liParams, $aParams, $beforeText, $afterText);
    }
    
	public function addLinkBlock($blockName)
    {
    	return parent::addLinkBlock($blockName);
    }
}