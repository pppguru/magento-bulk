<?php

class Extendware_EWPageCache_Model_Injector_Reports_Product_Compared extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$data = null;
		$cacheKey = $this->getCacheKey($params);
		$cache = $this->loadFromCache($cacheKey);
		if ($cache !== false) $data = $cache['data'];
		else {
			if (!Mage::getSingleton('log/visitor')->getId()) {
				$visitorId = Mage::getSingleton('core/session')->getData('visitor_data/visitor_id');
				if ($visitorId > 0) Mage::getSingleton('log/visitor')->load($visitorId);
			}
			$block = Mage::app()->getLayout()->createBlock('reports/product_compared', $this->getId());
			if (empty($params['template']) === true) {
				$params['template'] = 'reports/product_compared.phtml';
			}
			$block->setTemplate($params['template']);
			$data = $block->toHtml();
			$this->saveToCache($cacheKey, $data);
		}
		return $data;
	}
}
