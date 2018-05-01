<?php

class Extendware_EWPageCache_Model_Injector_Example extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		// can be a block like this
		/*$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
		$block->setTemplate('extendware/ewpagecache/welcome/message.phtml');
		$block->setIsLoggedIn(Mage::getSingleton('customer/session')->isLoggedIn());
		$content = $block->toHtml();*/
		
		// or just process it here
		$content = 'example text';
		return $content;
	}
	
	// ignore this as it is not relevant to holepunches.
	/*// if using the ajax method this is the javascript that will be used to replace the content on the page.
	// you can change it to suit your needs.
	public function processAjax(array $params = array(), array $request = array()) {
		return sprintf('$(%s).replace(%s);', json_encode($this->getPlaceholderId()), json_encode($this->getInjection($params, $request)));
	}
	
	// when using ajax mode this is the placeholder text that will eventually get replaced by the ajax call and what is returned by processAjax()
	// you can replace this with some default text 
	public function getPlaceholder(array $params = array(), array $request = array()) {
		// usually processAjax() depends on some div being present with the placeholder ID so it knows what to replace
		return sprintf('<div id="%s" style="display:none"></div>', $this->getPlaceholderId());
	}*/
}
