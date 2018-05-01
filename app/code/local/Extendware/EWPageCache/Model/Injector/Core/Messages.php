<?php

class Extendware_EWPageCache_Model_Injector_Core_Messages extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$block = Mage::app()->getLayout()->createBlock('core/messages', $this->getId());
		if (empty($params['template']) === false) $block->setTemplate($params['template']);
		$sessionKeys = array('core/session', 'catalog/session', 'checkout/session', 'customer/session');
		foreach ($sessionKeys as $key) {
			$messages = Mage::getSingleton($key)->getMessages(true);
			if ($messages and $messages->count() > 0) $block->addMessages($messages);
		}
		
		return $block->toHtml();
	}
}
