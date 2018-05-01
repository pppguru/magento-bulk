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
class MDN_SalesOrderPlanning_Block_ProductAvailabilityRange_List extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
	 * Return store view list
	 *
	 */
	public function getStoreViewList()
	{
		$stores = Mage::getModel('core/store')->getCollection();

		return $stores;
	}
	
	/**
	 * Return ranges
	 *
	 */
	public function getRanges()
	{
		return mage::helper('SalesOrderPlanning/ProductAvailabilityRange')->getConfig();
	}
	
		
	/**
	 * Return value for a store
	 *
	 * @param unknown_type $store
	 */
	public function getNewRangeUrl()
	{
		return $this->getUrl('adminhtml/SalesOrderPlanning_ProductAvailabilityRange/AddRange');
	}
	
	public function getRangeImageUrl($i)
	{
		return mage::helper('SalesOrderPlanning/ProductAvailabilityRange')->getRangeImageUrl($i);
	}
}