<?php
class Bulksupplements_RestrictCountry_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getNonShippableProducts($isAdmin=false)
	{
		$quote = $this->getQuote($isAdmin);
		$wantToShip   = $this->getShippingCountry($quote);
		$nonShippable = array();		
		foreach($quote->getAllItems() as $item) {
			$product = $item->getProduct();		
			if(!$this->canShipToCountry($product, $wantToShip)){
				$nonShippable[] = $product;
			}				
		}
		return $nonShippable;
	}
	
	public function removeNonShippableProducts($isAdmin=false)
	{
		$quote = $this->getQuote($isAdmin);
		$wantToShip   = $this->getShippingCountry($quote);
		foreach($quote->getAllItems() as $item) {
			$product = $item->getProduct();		
			if(!$this->canShipToCountry($product, $wantToShip)){
				$quote->removeItem($item->getId());
			}				
		}
		if($isAdmin){
			$quote->collectTotals()->save();
		}
		else{
			$quote->save();
		}
	}
	
	private function canShipToCountry($product, $toShipCountry)
	{
		$canShip = true;			
		if ($this->checkIfRestricted($product, $toShipCountry)) {
			$canShip = false;
		}
				
		//If it is true, then check for parents restricted countries
		if($canShip)
		{
			//Check if this simple product is a part of a grouped product and get the parent id(s)
			$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
			if(!$parentIds){
				//Check if this simple product has a child of a configurable product and get the parent id(s)
				$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
			}
			if(isset($parentIds[0])){
				foreach($parentIds as $key => $value){
					$parent = Mage::getModel('catalog/product')->load($value);
					if ($this->checkIfRestricted($parent, $toShipCountry)) {
						$canShip = false;
						break;
					}
				}
			}
		}		
		return $canShip;
	}
	
	private function checkIfRestricted($product, $toShipCountry)
	{
		$isRestricted = false;
		$restrictedCountries =  $product->getBsRestrictedCountries();
		if (is_string($restrictedCountries)){
			$restrictedCountries = explode(',', $restrictedCountries);
		}		
		if(in_array($toShipCountry, $restrictedCountries)){
			$isRestricted = true;
		}
		return $isRestricted;
	}
	
	private function getQuote($isAdmin=false)
	{
		if($isAdmin){
			return Mage::getSingleton('adminhtml/session_quote')->getQuote();
		}
		else{
			return Mage::getModel('checkout/cart')->getQuote();
		}
	}
	
	private function getShippingCountry($quote)
	{
		return $quote->getShippingAddress()->getCountry();		
	}	
}
