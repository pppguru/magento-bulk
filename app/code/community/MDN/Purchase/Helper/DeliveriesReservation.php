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
class MDN_Purchase_Helper_DeliveriesReservation extends Mage_Core_Helper_Abstract
{
	/**
	 * Check if expected delivery for one product will be assigned to an order 
	 *
	 * @param unknown_type $order
	 * @param unknown_type $product
	 */
	public function orderIsPriorForPurchaseOrderDelivery($order, $product)
	{
		$value = false;
		
		//if there is expected qty from purchase order
		$waitingForDeliveryQty = $product->getwaiting_for_delivery_qty();
		if ($waitingForDeliveryQty > 0)
		{
			//parse pending Orders
			$productId = $product->getId();
			$pendingOrders = mage::helper('AdvancedStock/Product_Base')->getPendingOrders($productId);
			foreach ($pendingOrders as $pendingOrder)
			{
				//get remaining to deliver qty for order and for product
				$remainingQtyToShipForPendingOrder = $this->getRemainingQtyToShipForOrder($pendingOrder, $product->getId());

				//if current order is order to check
				if (($remainingQtyToShipForPendingOrder <= $waitingForDeliveryQty) && ($order->getId() == $pendingOrder->getId()))
					return true;
				
				$waitingForDeliveryQty -= $remainingQtyToShipForPendingOrder;
			}
			
			//if it still qty, return true
			if ($waitingForDeliveryQty > 0)
				return true;
		}
		
		return $value;
	}
	
	protected function getRemainingQtyToShipForOrder($order, $productId)
	{
		foreach ($order->getAllItems() as $orderItem)
		{
			if ($orderItem->getproduct_id() == $productId)
			{
				$value = $orderItem->getRemainToShipQty() - $orderItem->getreserved_qty();
				if ($value < 0)
					$value = 0;
				return $value;
			}
		}
		
		return 0;
	}
}