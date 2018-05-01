<?php
class Extendware_EWPageCache_Model_Injector_Sales_Reorder_Sidebar extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$data = null;
		$cacheKey = $this->getCacheKey($params);
		$cache = $this->loadFromCache($cacheKey);
		if ($cache !== false) $data = $cache['data'];
		else {
			$block = Mage::app()->getLayout()->createBlock('sales/reorder_sidebar', $this->getId());
			$formKeyBlock = Mage::app()->getLayout()->createBlock('core/template')->setTemplate('core/formkey.phtml');
			Mage::app()->getLayout()->setBlock('formkey', $formKeyBlock);
			
			if (empty($params['template']) === true) {
				$params['template'] = 'sales/reorder/sidebar.phtml';
			}
			$block->setTemplate($params['template']);
			$data = $block->toHtml();
			$this->saveToCache($cacheKey, $data);
		}
		return $data;
	}
}
