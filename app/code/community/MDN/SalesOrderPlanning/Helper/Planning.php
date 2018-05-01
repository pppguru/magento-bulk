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
class MDN_SalesOrderPlanning_Helper_Planning extends Mage_Core_Helper_Abstract
{
	/**
	 * Plan planning update using order id
	 *
	 * @param unknown_type $orderId
	 */
	public function planPlanningUpdate($orderId)
	{
		$planning = mage::getModel('SalesOrderPlanning/Planning')->load($orderId, 'psop_order_id');
		if ($planning->getId())
		{
			$planning->setpsop_need_update(1)->save();

		}
	}


	/**
	 * Return planning for order
	 *
	 */
	public function getPlanningForOrder($order)
	{
		$planning = mage::getModel('SalesOrderPlanning/Planning')->load($order->getId(), 'psop_order_id');
		if ($planning->getId())
			return $planning;
		else
			return null;
	}

	/**
	 * Return planning for order
	 *
	 */
	public function getPlanningForOrderId($orderId)
	{
		$planning = mage::getModel('SalesOrderPlanning/Planning')->load($orderId, 'psop_order_id');
		if ($planning->getId())
			return $planning;
		else
			return null;
	}

	/**
	 * Helper to create planning for an order
	 *
	 * @param unknown_type $order
	 */
	public function createPlanning($order)
	{
		//delete planning if exists (as we create it :))
		try {
			$planning = mage::getModel('SalesOrderPlanning/Planning')->load($order->getId(), 'psop_order_id');
			if ($planning->getId())
				$planning->delete();

			//create & init information
			$planning = mage::getModel('SalesOrderPlanning/Planning');
			$planning->setpsop_order_id($order->getId());

			//avoid 1970 issue when payment validated and planing is reseted, we where loose the psop_real_payment_date
			$planning->adjustPaymentDate($order);

			$planning->setConsiderationInformation($order);
			$planning->setFullStockInformation($order);
			$planning->setShippingInformation($order);
			$planning->setDeliveryInformation($order);
			$planning->copyAnnouncedDateFromOrder($order);

			$planning->save();
		}catch(Exception $ex){
			$message = 'createPlanning : Unable to Create Planning order #'.$order->getId().' ex='.$ex->getMessage().' - '.$ex->getTraceAsString();
			mage::log($message,null,'erp_planning_creation.log');
		}
		return $planning;
	}


	public function createPlanningFromOrderId($orderId)
	{
		$order = mage::getModel('sales/order')->load($orderId);
		$this->createPlanning($order);
	}

	/**
	 * Return estimated delivery date for a quote
	 * return planning object
	 * @param unknown_type $quote
	 */
	public function getEstimationForQuote($quote)
	{
		//calculate planning
		$planning = mage::getModel('SalesOrderPlanning/Planning');
		$planning->setConsiderationInformation($quote, true);
		$planning->setFullStockInformation($quote, true);
		$planning->setShippingInformation($quote, true);
		$planning->setDeliveryInformation($quote, true);

		$planning->setpsop_anounced_date($planning->getpsop_delivery_date());
		$planning->setpsop_anounced_date_max($planning->getpsop_delivery_date_max());

		return $planning;
	}

	/**
	 * Update planning (method to use when information for the order changes (product reservation, payment, expedition ...)
	 *
	 * @param unknown_type $orderId
	 */
	public function updatePlanning($orderId)
	{
		//mage::log('##Start update planning for order #'.$orderId);
		$order = mage::getModel('sales/order')->load($orderId);
		if ($order->getId())
		{
			$planning = $this->getPlanningForOrder($order);
			if ($planning)
			{
				if ($planning->getConsiderationDate() == null)
				{
					mage::log('Set condideration date');
					$planning->setConsiderationInformation($order);
				}

				$planning->setFullStockInformation($order);
				$planning->setShippingInformation($order);
				$planning->setDeliveryInformation($order);
				$planning->setpsop_need_update(0);
				$planning->save();
			}
		}
		else {
			$message = 'updatePlanning : Unable to load order #'.$order->getId();
			mage::log($message,null,'erp_planning_creation.log');
		}
		//mage::log('##End update planning for order #'.$orderId);

	}

	/**
	 * Store real fullstock date
	 *
	 * @param unknown_type $orderId
	 */
	public function storeRealFullStockDate($orderId)
	{
		$order = mage::getModel('sales/order')->load($orderId);
		if($order->getId()>0) {
			$planning = mage::helper('SalesOrderPlanning/Planning')->getPlanningForOrder($order);
			if ($planning) {
				if (Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->allProductsAreReserved($order)) {
					$realFullStockDate = date('Y-m-d');
				} else {
					$realFullStockDate = new Zend_Db_Expr('null');
				}
				$planning->setpsop_real_fullstock_date($realFullStockDate)->save();
			}
		}
	}

	/**
	 * Store payment date
	 *
	 * @param unknown_type $orderId
	 */
	public function storePaymentDate($orderId)
	{
		try{

			$order = mage::getModel('sales/order')->load($orderId);
			$planning = mage::helper('SalesOrderPlanning/Planning')->getPlanningForOrder($order);

			//FIX :
			if ($planning == null){
				$this->createPlanning($order);
			}

			//LOGS :
			if ($planning == null){
				$message = 'storePaymentDate : Unable to get Planning order #'.$orderId;
				mage::log($message,null,'erp_planning_creation.log');
				return false;
			}

			if ($planning->getId())
			{

				$planning->adjustPaymentDate($order);

				$planning
						->setpsop_consideration_date(new Zend_Db_Expr('null'))
						->setpsop_consideration_date_max(new Zend_Db_Expr('null'))
						->setpsop_fullstock_date(new Zend_Db_Expr('null'))
						->setpsop_fullstock_date_max(new Zend_Db_Expr('null'))
						->setpsop_shipping_date(new Zend_Db_Expr('null'))
						->setpsop_shipping_date_max(new Zend_Db_Expr('null'))
						->setpsop_delivery_date(new Zend_Db_Expr('null'))
						->setpsop_delivery_date_max(new Zend_Db_Expr('null'))
						->save();
			}

			//add a task to update order planning
			$this->updatePlanning($orderId);
		}catch(Exception $ex){
			$message = 'storePaymentDate : Unable to update Planning order #'.$orderId.' ex='.$ex->getMessage().' - '.$ex->getTraceAsString();
			mage::log($message,null,'erp_planning_creation.log');
		}

	}



}