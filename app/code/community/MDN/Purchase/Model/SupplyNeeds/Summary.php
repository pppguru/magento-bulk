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
class MDN_Purchase_Model_SupplyNeeds_Summary  extends Mage_Core_Model_Abstract
{
	private $_suppliers = null;
	
	/**
	 * Get all suppliers for existing supply needs
	 */
	public function getSuppliers()
	{
		if ($this->_suppliers == null)
		{
			$this->_suppliers = null;
		}
		return $this->_suppliers;
	}
	
	/**
	 * 
	 */
	public function getAmount($supplier, $supplyNeedStatus)
	{
	
	}
	
	/**
	 *
	 */
	public function getStatuses()
	{
		return mage::getModel('cataloginventory/stock_item')->getStatuses();
	}
	
}