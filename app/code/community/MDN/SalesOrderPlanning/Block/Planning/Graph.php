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
class MDN_SalesOrderPlanning_Block_Planning_Graph extends Mage_Core_Block_Template
{
	private $_planning = null;
	private $_order = null;
	
	/**
	 * Return planning
	 *
	 */
	public function getPlanning()
	{
		if ($this->_planning == null)
		{
			$this->_planning = mage::helper('SalesOrderPlanning/Planning')->getPlanningForOrder($this->getOrder());
			//if planning is not up to date, refresh it
			if ($this->_planning)
			{
				if ($this->_planning->getpsop_need_update() == 1)
				{
					mage::helper('SalesOrderPlanning/Planning')->updatePlanning($this->getOrder()->getId());
					$this->_planning = mage::helper('SalesOrderPlanning/Planning')->getPlanningForOrder($this->getOrder());
				}			
			}
		}
		return $this->_planning;
	}
	
	/**
	 * Return true if planning is available
	 *
	 */
	public function planningAvailable()
	{
		return ($this->getPlanning() != null);
	}
	
	
	/**
	 * Enter description here...
	 *
	 */
	public function getOrder()
	{
		return $this->_order;
	}
	public function setOrder($order)
	{
		$this->_order = $order;
		return $this;
	}
	
	/**
	 * 
	 *
	 */
	public function IsConsidered()
	{
		$value = ($this->getPlanning()->getpsop_consideration_date() != null);
		return $value;
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function isPrepared()
	{
		return $this->isShipped();
	}
	
	/**
	 * 
	 *
	 */
	public function allProductsAreReserved()
	{
		return Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->allProductsAreReserved($this->getOrder());
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function isShipped()
	{
		return Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->IsCompletelyShipped($this->getOrder());
	}
	
	/**
	 * Enter description here...
	 *
	 */
	public function getEstimatedDeliveryDate()
	{
		if ($this->getPlanning())
			return $this->getPlanning()->getDeliveryDate();
	}
}