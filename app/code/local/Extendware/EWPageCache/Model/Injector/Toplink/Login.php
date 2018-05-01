<?php

class Extendware_EWPageCache_Model_Injector_Toplink_Login extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
		$block->setIsLoggedIn(Mage::getSingleton('customer/session')->isLoggedIn());;
		
		if (empty($params['template']) === true) {
			$params['template'] = 'extendware/ewpagecache/toplink/login.phtml';
		}
		$block->setTemplate($params['template']);
			
		return $block->toHtml();
	}
}
