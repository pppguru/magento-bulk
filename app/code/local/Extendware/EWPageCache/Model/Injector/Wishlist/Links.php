<?php
class Extendware_EWPageCache_Model_Injector_Wishlist_Links extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('wishlist/links', $this->getId());
		$block->setTemplate($params['template']);
		return $block->toHtml();
	}
}
