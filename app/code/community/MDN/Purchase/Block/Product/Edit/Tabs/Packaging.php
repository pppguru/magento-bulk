<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Block_Product_Edit_Tabs_Packaging extends Mage_Adminhtml_Block_Widget_Form
{
	private $_packages = null;
	
	/**
	 * Current product
	 *
	 * @var unknown_type
	 */
	private $_product = null;
	public function setProduct($Product)
	{
		$this->_product = $Product;
		return $this;
	}
	public function getProduct()
	{
		return $this->_product;
	}	

	/**
	 * Return packages for current product
	 *
	 * @return unknown
	 */
	public function getPackagings()
	{
		if ($this->_packages == null)
		{
			$this->_packages = mage::helper('purchase/Product_Packaging')->getPackagingForProduct($this->getProduct()->getId());
		}
		return $this->_packages;
	}

	/**
	 * Return false if there is no default sales package for product
	 *
	 */
	public function hasDefaultSalesPackage()
	{
		foreach ($this->getPackagings() as $package)
		{
			if ($package->getpp_is_default_sales() == 1)
				return true;
		}
		return false;
	}
	
	/**
	 * Return false if there is no default purchase package for product
	 *
	 */
	public function hasDefaultPurchasePackage()
	{
		foreach ($this->getPackagings() as $package)
		{
			if ($package->getpp_is_default() == 1)
				return true;
		}
		return false;		
	}
}