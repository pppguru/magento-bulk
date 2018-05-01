<?php
class Bulksupplements_RestrictCountry_Adminhtml_RestrictcountryController extends Mage_Adminhtml_Controller_Action
{	
	//Test Action
	public function indexAction()
	{
		echo "restrict shipping";
	}
	
	//Action that is executed when Continue button is hit in case there are non shippable products.
	//It first removes the non shippable products and then redirect to the same page again
	public function removeNonShippableProductsAction()
	{
		Mage::helper('restrictcountry')->removeNonShippableProducts(true);
		$this->_redirect('*/sales_order_create', array('_secure'=>true));
	}
}

