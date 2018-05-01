<?php
class Bulksupplements_RestrictCountry_IndexController extends Mage_Core_Controller_Front_Action 
{	
	//Test Action
	public function indexAction()
	{
		echo "restrict shipping";
	}
	
	//Action that is executed when Continue button is hitted in case there are non shippable products.
	//It first removes the non shippable products and then redirect to onestepcheckout page again
	public function removeNonShippableProductsAction()
	{
		Mage::helper('restrictcountry')->removeNonShippableProducts();
		$this->_redirect('onestepcheckout', array('_secure'=>true));
	}
}

