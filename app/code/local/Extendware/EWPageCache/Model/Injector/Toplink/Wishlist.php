<?php

class Extendware_EWPageCache_Model_Injector_Toplink_Wishlist extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$data = null;
		$cacheKey = $this->getCacheKey($params);
		$cache = $this->loadFromCache($cacheKey);
		if ($cache !== false) $data = $cache['data'];
		else {
			$data = '';
			if (Mage::helper('wishlist')->isAllow()) {
				$block = Mage::app()->getLayout()->createBlock('core/template', $this->getId());
				$block->setQty(Mage::helper('wishlist')->getItemCount());
				
				if (empty($params['template']) === true) {
					$params['template'] = 'extendware/ewpagecache/toplink/wishlist.phtml';
				}
				$block->setTemplate($params['template']);
				$data = $block->toHtml();
			}
			$this->saveToCache($cacheKey, $data);
		}
		return $data;
	}
}
