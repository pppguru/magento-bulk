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
class MDN_Purchase_Helper_Product_Cost extends MDN_AdvancedStock_Helper_Product_Cost
{
	
	/**
	 * Return product cost
	 *
	 * @param unknown_type $product
	 * @param unknown_type $date
	 */
	public function getProductCostAtDate($product, $date, $qty, $warehouse)
	{		
		$cost = 0;
		$productId = $product->getId();
		$productName = $product->getName();
		
		//add one day to date
		$dateTimestamp = strtotime($date);
		$dateTimestamp += 3600 * 24;
		$date = date('Y-m-d', $dateTimestamp);
		
		//collect stock movement with join on purchase order & purchase order item
		$collection = mage::getModel('AdvancedStock/StockMovement')
						->getCollection()
						->addFieldToFilter('sm_product_id', $productId)
						->addFieldToFilter('sm_po_num', array('gt' => 0))
						->addFieldToFilter('sm_date', array('lt' => $date))
						->addFieldToFilter('sm_target_stock', $warehouse->getId())
						->setOrder('sm_id', 'desc');
						
		$priceSum = 0;
		$sourceCount = 0;
		$priceCount = 0;
		foreach ($collection as $sm)
		{
			//define qty to use
			$consideredQty = $qty;
			if ($consideredQty > $sm->getsm_qty())
				$consideredQty = $sm->getsm_qty();

			//retrieve PO item
			$poItem = null;
			$orderId = $sm->getsm_po_num();
			$poItemCollection = mage::getModel('Purchase/OrderProduct')
									->getCollection()
									->addFieldToFilter('pop_product_id', $productId)
									->addFieldToFilter('pop_order_num', $orderId);
			foreach ($poItemCollection as $item)
			{
				$poItem = $item;
				break;
			}
			
			//add product prices
			if ($poItem != null)
			{
				$unitPriceWithECost = $poItem->getUnitPriceWithExtendedCosts_base();
				$priceSum += $consideredQty *$unitPriceWithECost;
				$priceCount += $consideredQty;
				$this->debug .= ' (consider '.$consideredQty.' coming from PO # '.$sm->getsm_po_num().' with cost = '.$unitPriceWithECost.' ) ';
				
				//decrease stock
				$qty -= $consideredQty;
				if ($qty == 0)
					break;
			}
		}
			
		if ($priceCount > 0)
			$cost = number_format($priceSum / $priceCount, 2);		
		if ($cost == 0)
			$cost = parent::getProductCostAtDate($product, $date, $qty, $warehouse);
		if ($sourceCount > 1)
			$this->debug .= 'Product '.$productName.' stock comes from '.$sourceCount.' sources (price = '.$cost.')'."\n";
		if ($sourceCount == 0)
			$this->debug .= 'Product '.$productName.' stock comes from 0 sources (price = '.$cost.')'."\n";

			
		return $cost;
		
	}
}