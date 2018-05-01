<?php
class Bulksupplements_CustomOrder_Model_Observer{
	/**
	* This function is called just before $quote object get stored to database.
	* Here, from POST data, we capture our custom field and put it in the quote object
	* @param unknown_type $evt
	*/
	public function saveQuoteBefore($evt){
		$quote = $evt->getQuote();
		$post = Mage::app()->getFrontController()->getRequest()->getPost();
		if(isset($post['po_number'])){
			$var = $post['po_number'];
			$quote->setPonumber($var);
		}
	}
	/**
	* This function is called, just after $quote object get saved to database.
	* Here, after the quote object gets saved in database
	* we save our custom field in the our table created i.e sales_quote_custom
	* @param unknown_type $evt
	*/
	public function saveQuoteAfter($evt){
		$quote = $evt->getQuote();
		if($quote->getPonumber()){
			$var = $quote->getPonumber();
			if(!empty($var)){
				$model = Mage::getModel('customorder/customorder_quote');
				$model->deteleByQuote($quote->getId(),'ponumber');
				$model->setQuoteId($quote->getId());
				$model->setKey('ponumber');
				$model->setValue($var);
				$model->save();
			}
		}
	}
	/**
	*
	* When load() function is called on the quote object,
	* we read our custom fields value from database and put them back in quote object.
	* @param unknown_type $evt
	*/
	public function loadQuoteAfter($evt){
		$quote = $evt->getQuote();
		$model = Mage::getModel('customorder/customorder_quote');
		$data = $model->getByQuote($quote->getId());
		foreach($data as $key => $value){
			$quote->setData($key,$value);
		}
	}
	/**
	*
	* This function is called after order gets saved to database.
	* Here we transfer our custom fields from quote table to order table i.e sales_order_custom
	* @param $evt
	*/
	public function saveOrderAfter($evt){
		$order = $evt->getOrder();
		$quote = $evt->getQuote();
		if($quote->getPonumber()){
			$var = $quote->getPonumber();
			if(!empty($var)){
				$model = Mage::getModel('customorder/customorder_order');
				$model->deleteByOrder($order->getId(),'ponumber');
				$model->setOrderId($order->getId());
				$model->setKey('ponumber');
				$model->setValue($var);
				$order->setPonumber($var);
				$model->save();
			}
		}
	}
	/**
	*
	* This function is called when $order->load() is done.
	* Here we read our custom fields value from database and set it in order object.
	* @param unknown_type $evt
	*/
	public function loadOrderAfter($evt){
		$order = $evt->getOrder();
		$model = Mage::getModel('customorder/customorder_order');
		$data = $model->getByOrder($order->getId());
		foreach($data as $key => $value){
			$order->setData($key,$value);
		}
	}
}