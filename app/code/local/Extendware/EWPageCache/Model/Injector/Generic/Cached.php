<?php

class Extendware_EWPageCache_Model_Injector_Generic_Cached extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		if (isset($params['template']) and isset($params['type'])) {
			return null;
		}
		$data = null;
		$cacheKey = $this->getCacheKey($params);
		$cache = $this->loadFromCache($cacheKey);
		if ($cache !== false) $data = $cache['data'];
		else {
			$block = Mage::app()->getLayout()->createBlock($params['type'], $this->getId());
			$block->setTemplate($params['template']);
			$data = $block->toHtml();
			$this->saveToCache($cacheKey, $data);
		}
		return $data;
	}
	
	protected function getCacheKeyData(array $data = array()) {
		return json_encode($data);
	}
}
