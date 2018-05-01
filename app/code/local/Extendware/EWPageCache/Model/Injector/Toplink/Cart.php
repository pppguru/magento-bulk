<?php

class Extendware_EWPageCache_Model_Injector_Toplink_Cart extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$data = null;
		$cacheKey = $this->getCacheKey($params);
		$cache = $this->loadFromCache($cacheKey);
		if ($cache !== false) $data = $cache['data'];
		else {
			$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
			$block->setQty((int)Mage::getSingleton('checkout/cart')->getSummaryQty());
			
			if (empty($params['template']) === true) {
				$params['template'] = 'extendware/ewpagecache/toplink/cart.phtml';
			}
			$block->setTemplate($params['template']);
			$data = $block->toHtml();
			$this->saveToCache($cacheKey, $data);
		}
		return $data;
	}
}
