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
class MDN_SalesOrderPlanning_Block_ShippingDelay_List extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
	 * return list
	 *
	 */
	public function getList()
	{
		$collection = mage::getModel('SalesOrderPlanning/ShippingDelay')->getCollection();
		return $collection;
	}
	
	/**
	 * Return url to update carrier list
	 *
	 */
	public function getUpdateCarrierUrl()
	{
		return $this->getUrl('adminhtml/SalesOrderPlanning_ShippingDelay/UpdateCarriers');
	}
}