<?php

class Extendware_EWPageCache_Model_Injector_Reports_Product_Viewed extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		if (!Mage::getSingleton('log/visitor')->getId()) {
			$visitorId = Mage::getSingleton('core/session')->getData('visitor_data/visitor_id');
			if ($visitorId > 0) Mage::getSingleton('log/visitor')->load($visitorId);
		}

		$params = $request['params'];
		$productId = isset($params['id']) ? $params['id'] : 0;
		$block = Mage::app()->getLayout()->createBlock('reports/product_widget_viewed', $this->getId());

		if (empty($params['template']) === true) {
			$params['template'] = 'reports/product_viewed.phtml';
		}
		$block->setTemplate($params['template']);
		if ($productId and $request['controller'] == 'product' and $request['action'] == 'view') {
			$block->setActiveProductId($productId);
		}
		
		return $block->toHtml();
	}
}
