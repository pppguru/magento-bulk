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
class MDN_SalesOrderPlanning_Block_Product_Edit_Tabs_AvailabilityStatus extends Mage_Adminhtml_Block_Template
{
	private $_availabilityStatus = null;
	
	/**
	 * Product get/set
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
	
	public function getAvailabilityStatus()
	{
		if ($this->_availabilityStatus == null)
		{
			$productId = $this->getProduct()->getId();
			$this->_availabilityStatus = mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->getForOneProduct($productId);
		}
		return $this->_availabilityStatus;
	}
	
	/**
	 * Constructeur
	 *
	 */
	public function __construct()
	{
		parent::__construct();
				
	}

	public function getRefreshUrl()
	{
		return $this->getUrl('adminhtml/SalesOrderPlanning_ProductAvailabilityStatus/RefreshProductAndGoBackToProductSheet', array('product_id' => $this->getProduct()->getId()));
	}
	
}