<?php

class Extendware_EWPageCache_Model_Injector_Welcome_Message extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
		$block->setIsLoggedIn(Mage::getSingleton('customer/session')->isLoggedIn());
		
		if (empty($params['template']) === true) {
			$params['template'] = 'extendware/ewpagecache/welcome/message.phtml';
		}
		$block->setTemplate($params['template']);
		return $block->toHtml();
	}
	
	public function processAjax(array $params = array(), array $request = array()) {
		return sprintf('$(%s).replace(%s);', json_encode($this->getPlaceholderId()), json_encode($this->getInjection($params, $request)));
	}
	
	public function getPlaceholder(array $params = array(), array $request = array()) {
		return sprintf('<span id="%s"></span>', $this->getPlaceholderId());
	}
}
