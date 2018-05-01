<?php

class Extendware_EWPageCache_Model_Injector_Generic_Uncached extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		if (isset($params['template']) and isset($params['type'])) {
			return null;
		}
		
		$block = Mage::app()->getLayout()->createBlock($params['type'], $this->getId());
		$block->setTemplate($params['template']);
		return $block->toHtml();
	}
}
