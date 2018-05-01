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
class MDN_AdvancedStock_Helper_Product_Serial extends Mage_Core_Helper_Abstract
{
	/**
	 * Import serials from delivery
	 *
	 * @param unknown_type $productId
	 * @param unknown_type $purchaseOrder
	 * @param unknown_type $serialsString
	 */
	public function addSerialsFromDelivery($productId, $purchaseOrder, $serialsString)
	{
		$t = explode("\r\n", $serialsString);
		foreach ($t as $serial)
		{
			$serial = trim($serial);
			if ($serial == '')
				continue;

			//if not exist, insert
			if (!$this->serialExists($serial))
			{
				mage::getModel('AdvancedStock/ProductSerial')
						->setpps_product_id($productId)
						->setpps_purchaseorder_id($purchaseOrder->getId())
						->setpps_serial($serial)
						->save();
			}
		}
	}
	
	/**
	 * Check if a serial already exists
	 *
	 * @param unknown_type $serial
	 * @return unknown
	 */
	public function serialExists($serial)
	{
		$collection = mage::getModel('AdvancedStock/ProductSerial')
										->getCollection()
										->addFieldToFilter('pps_serial', $serial);
		return ($collection->getSize() > 0);
	}
	
	/**
	 * Add serials for product
	 *
	 * @param unknown_type $productId
	 * @param unknown_type $string
	 */
	public function addSerialsFromString($productId, $serialsString)
	{
		$serialsInserted = 0;
		$t = explode("\r\n", $serialsString);
		foreach ($t as $serial)
		{
			$serial = trim($serial);
			if ($serial == '')
				continue;

			//if not exist, insert
			if (!$this->serialExists($serial))
			{
				mage::getModel('AdvancedStock/ProductSerial')
						->setpps_product_id($productId)
						->setpps_serial($serial)
						->save();
				$serialsInserted++;
			}
		}
		return $serialsInserted;
	}
	
	/**
	 * Create association between serial and sales order
	 *
	 * @param unknown_type $salesOrder
	 * @param unknown_type $serialsString
	 */
	public function linkSalesOrderToSerial($salesOrder, $serialsString, $productId, $shipmentItemId)
	{
		$t = explode("\n", $serialsString);
		foreach ($t as $serial)
		{
			$serial = str_replace("\r", '', $serial);
			$serial = trim($serial);
			if ($serial == '')
				continue;
		
			//retrieve serial record
			$serialItem = mage::getModel('AdvancedStock/ProductSerial')->load($serial, 'pps_serial');
			if ($serialItem->getId())
				$serialItem->setpps_salesorder_id($salesOrder->getId())->setpps_shipment_item_id($shipmentItemId)->save();

		}
	}
	
	
	public function unlinkSerialsToShipment($shipment)
	{
    	$collection = mage::getModel('AdvancedStock/ProductSerial')
						->getCollection()
						->addFieldToFilter('pps_shipment_item_id', $shipment->getId());

		foreach($collection as $item)
		{
			$item->setpps_shipment_item_id(new Zend_Db_Expr('null'))
					->setpps_salesorder_id(new Zend_Db_Expr('null'))
					->save();
		}
	}
	
}