<?php
class Extendware_EWPageCache_Model_Injector_Misc_Review extends Extendware_EWPageCache_Model_Injector_Abstract
{
	public function getInjection(array $params = array(), array $request = array()) {
		$productId = $params['product_id'];
		if (!Mage::registry('product')) {
			$product = Mage::getModel('catalog/product')->load($productId);
			Mage::register('product', $product);
		}
		$block3 = Mage::app()->getLayout()->createBlock('page/html_wrapper', $this->getId());
		$block3->setTemplate('form_fields_before');
		$block3->setMayBeVisible(1);
		
		$block2 = Mage::app()->getLayout()->createBlock('review/form', $this->getId());
		$block2->setTemplate('review/form.phtml');
		$block2->setChild('form_fields_before', $block3);
		
		$block = Mage::app()->getLayout()->createBlock('review/product_view_list', $this->getId());
		$block->setTemplate('review/product/view/list.phtml');
		$block->setChild('review_form', $block2);
	
		return $block->toHtml();
	}
}
