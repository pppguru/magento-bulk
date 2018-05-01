<?php

class Extendware_EWPageCache_Model_Injector_Welcome_Message_Block extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$header = @Mage::app()->getLayout()->getBlock('header');
		if ($header) { // avoids errors in some installations
			$block = Mage::app()->getLayout()->createBlock('page/html_welcome', $this->getId());
			return $block->toHtml();
		}
		
		// done as a backup method
		$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
		$block->setIsLoggedIn(Mage::getSingleton('customer/session')->isLoggedIn());
		
		if (empty($params['template']) === true) {
			$params['template'] = 'extendware/ewpagecache/welcome/message.phtml';
		}
		$block->setTemplate($params['template']);
		return $block->toHtml();
	}
}