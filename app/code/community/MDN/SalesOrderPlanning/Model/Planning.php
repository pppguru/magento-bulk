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
class MDN_SalesOrderPlanning_Model_Planning  extends Mage_Core_Model_Abstract
{

	/**
	 * Constructor
	 *
	 */
	public function _construct()
	{
		parent::_construct();
		$this->_init('SalesOrderPlanning/Planning');
	}

	/**
	 * return consideration date
	 *
	 */
	public function getConsiderationDate()
	{
		$retour = $this->getpsop_consideration_date();
		if ($this->getpsop_consideration_date_force() != '')
			$retour = $this->getpsop_consideration_date_force();
		return $retour;
	}

	/**
	 * return fullstock date
	 *
	 */
	public function getFullstockDate()
	{
		$retour = $this->getpsop_fullstock_date();
		if ($this->getpsop_fullstock_date_force() != '')
			$retour = $this->getpsop_fullstock_date_force();
		return $retour;
	}

	/**
	 * return shipping date
	 *
	 */
	public function getShippingDate()
	{
		$retour = $this->getpsop_shipping_date();
		if ($this->getpsop_shipping_date_force() != '')
			$retour = $this->getpsop_shipping_date_force();
		return $retour;
	}

	/**
	 * return shipping date
	 *
	 */
	public function getDeliveryDate()
	{
		$retour = $this->getpsop_delivery_date();
		if ($this->getpsop_delivery_date_force() != '')
			$retour = $this->getpsop_delivery_date_force();
		return $retour;
	}

	/*****************************************************************************************************************************
	 ******************************************************************************************************************************
	 ****************** Fill Sections *****************************************************************************************************
	 ******************************************************************************************************************************
	 ******************************************************************************************************************************/

	/**
	 * Define consideration information depending of order and parameters
	 *
	 */
	public function setConsiderationInformation($order, $quoteMode = false)
	{

		$considerationDateTimeStamp = null;
		$considerationComments = '';
		if (!$quoteMode) {
			$orderRealDatetime = $order->getCreatedAtStoreDate();
			if(!$orderRealDatetime) {
				$orderRealDatetime = $order->getCreatedAt();
			}
		}else {
			$orderRealDatetime = date('Y-m-d H:i:s');
		}

		if (!$quoteMode)
		{

			//init consider date when order placed
			if (Mage::getStoreConfig('planning/consider/consider_order_when_placed') == 1)
			{
				if(get_class($orderRealDatetime) == 'Zend_Date'){
					$considerationDateTimeStamp = strtotime($orderRealDatetime->toString());
				}else{
					$considerationDateTimeStamp = strtotime($orderRealDatetime);
				}

				$orderRealDatetime = Mage::helper('core')->formatTime($orderRealDatetime, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
				$considerationComments = mage::helper('SalesOrderPlanning')->__('Order placed on %s <br>', $orderRealDatetime);

				//if order placed after specific hour, add one day
				$maxHour = Mage::getStoreConfig('planning/consider/consider_order_tomorow_if_placed_after');
				if (is_numeric($maxHour) && $maxHour > 0 && $maxHour <=24 && date('G', $considerationDateTimeStamp) >= $maxHour)
				{
					$considerationDateTimeStamp += 3600 * 24;
					$considerationComments .=  mage::helper('SalesOrderPlanning')->__('add 1 day as order placed after %s h <br>', $maxHour);
				}
			}

			//init order information when order invoiced
			if (Mage::getStoreConfig('planning/consider/consider_order_when_invoiced') == 1)
			{
				$invoiceDate = $this->getOrderInvoicedDate($order);

				if ($invoiceDate != null)
				{

					if(!is_string($invoiceDate) && (get_class($invoiceDate) == 'Zend_Date')){
						$considerationDateTimeStamp = strtotime($invoiceDate->toString());
					}else{
						$considerationDateTimeStamp = strtotime($invoiceDate);

					}
					$considerationComments =  mage::helper('SalesOrderPlanning')->__('Order invoiced on %s <br>', $invoiceDate);

					//if order placed after specific hour, add one day
					$maxHour = Mage::getStoreConfig('planning/consider/consider_order_tomorow_if_placed_after');
					if (is_numeric($maxHour) && $maxHour > 0 && $maxHour <=24 && date('G', $considerationDateTimeStamp) >= $maxHour)
					{
						$considerationDateTimeStamp += 3600 * 24;
						$considerationComments .=  mage::helper('SalesOrderPlanning')->__('add 1 day as order placed after %s h <br>', $maxHour);
					}
					else
						$considerationComments .=  mage::helper('SalesOrderPlanning')->__('Order invoiced at %s h<br>', date('H', $considerationDateTimeStamp));
				}
				else {
					$considerationComments = mage::helper('SalesOrderPlanning')->__('Order not invoiced<br>');
					$considerationDateTimeStamp = null;
				}
			}

			//init order information when payment_validated
			if (Mage::getStoreConfig('planning/consider/consider_order_on_paypment_validated') == 1)
			{
				//todo : order is considered once it's valid, payment is not the right condition
				if ($order->getpayment_validated() == 1)
				{
					$considerationDateTimeStamp = strtotime($this->getpsop_real_payment_date());
					if($considerationDateTimeStamp) {
						$considerationComments = mage::helper('SalesOrderPlanning')->__('Payment validated on %s <br>', date('Y-m-d H:i', $considerationDateTimeStamp));

						//if order placed after specific hour, add one day
						$maxHour = Mage::getStoreConfig('planning/consider/consider_order_tomorow_if_placed_after');
						if (is_numeric($maxHour) && $maxHour > 0 && $maxHour <= 24 && date('G', $considerationDateTimeStamp) >= $maxHour) {
							$considerationDateTimeStamp += 3600 * 24;
							$considerationComments .= mage::helper('SalesOrderPlanning')->__('add 1 day as order placed after %s h <br>', $maxHour);
						} else
							$considerationComments .= mage::helper('SalesOrderPlanning')->__('Payment validated at %s h<br>', date('H', $considerationDateTimeStamp));
					}else{
						$considerationComments .=  mage::helper('SalesOrderPlanning')->__('Payment validation date not found, do not consider order<br>');
						$considerationDateTimeStamp = null;
					}
				}
				else
				{
					$considerationComments .=  mage::helper('SalesOrderPlanning')->__('Payment not validated, do not consider order<br>');
					$considerationDateTimeStamp = null;
				}
			}
		}
		else //if quote mode
		{
			if(get_class($orderRealDatetime) == 'Zend_Date'){
				$considerationDateTimeStamp = strtotime($orderRealDatetime->toString());
			}else{
				$considerationDateTimeStamp = strtotime($orderRealDatetime);
			}

			$considerationComments .=  mage::helper('SalesOrderPlanning')->__('Quote for today<br>');

			//if order placed after specifi hour, add one day
			$maxHour = Mage::getStoreConfig('planning/consider/consider_order_tomorow_if_placed_after');
			if (is_numeric($maxHour) && $maxHour > 0 && $maxHour <=24 && date('G', $considerationDateTimeStamp) >= $maxHour)
			{
				$considerationDateTimeStamp += 3600 * 24;
				$considerationComments .=  mage::helper('SalesOrderPlanning')->__('add 1 day as order placed after %s h <br>', $maxHour);
			}

			//add days depending of payment method
			$payment = $order->getPayment();
			if ($payment != null && $payment->getMethod() != null){
				$delay = $this->getPaymentDelay($payment->getMethod());
				if ($delay > 0)
				{
					$considerationDateTimeStamp += 3600 * 24 * $delay;
					$considerationComments .= 'Add '.$delay.' days for payment method ('.$payment->getMethod().') -> '.date('Y-m-d', $considerationDateTimeStamp).'<br>';
				}
			}
			else
				$considerationComments .=  mage::helper('SalesOrderPlanning')->__('No payment method<br>');
		}

		//add days to avoid holy day
		if (Mage::getStoreConfig('planning/consider/include_holy_days') == 0)
		{
			if ($considerationDateTimeStamp != null)
			{
				$daysToAdd = $this->DaysUntilNotHolyDay($considerationDateTimeStamp,$order->getStoreId());
				if ($daysToAdd > 0)
				{
					$considerationDateTimeStamp += 3600 * 24 * $daysToAdd;
					$considerationComments .=  mage::helper('SalesOrderPlanning')->__('add %s days to avoid holy day<br>', $daysToAdd);
				}
			}
		}

		//set consideration information
		if ($considerationDateTimeStamp != null)
		{
			$this->setpsop_consideration_date(date('Y-m-d', $considerationDateTimeStamp));
			$this->setpsop_consideration_comments($considerationComments);
		}
		else
		{
			mage::log('Consideration date is null');
			$this->setpsop_consideration_date(null);
			$this->setpsop_consideration_comments($considerationComments);
		}

		return $this;
	}

	/**
	 * Set full stock information
	 *
	 * @param unknown_type $order
	 */
	public function setFullStockInformation($order, $quoteMode = false)
	{
		$considerationDate = $this->getConsiderationDate();
		if ($considerationDate == null)
		{
			$this->setpsop_fullstock_date(null);
			$this->setpsop_fullstock_date_max(null);
			$this->setpsop_fullstock_comments('');
			return $this;
		}

		//init vars
		$ProductInformation = '';
		$beginingDate = strtotime($considerationDate);
		$ProductInformation .= mage::helper('SalesOrderPlanning')->__('Beginning date is : ').$considerationDate.'<br>';
		$allProductReserved = true;
		$worstSupplyDate = $beginingDate;
		$ParentProductsWhichOverridePlanning = array();
		$websiteId = 0;
		if ($quoteMode)
			$websiteId = Mage::app()->getStore()->getwebsite_id();

		//parse products collection first to define $ParentProductsWhichOverridePlanning
		foreach($order->getItemsCollection() as $item)
		{
			$productId = $item->getproduct_id();
			$product = mage::getModel('catalog/product')->load($productId);
			$item->setProduct($product);
			if ($product->getoverride_subproducts_planning() == 1)
			{
				$productAvailabilityStatus = mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->getForOneProduct($productId);
				$ParentProductsWhichOverridePlanning[$productId] = $productAvailabilityStatus->getpa_supply_delay();
			}
		}

		//browse products
		foreach($order->getItemsCollection() as $item)
		{
			$productId = $item->getproduct_id();
			if(!$productId)
				continue;
			if (!$quoteMode)
			{
				$remaining_qty = $item->getRemainToShipQty();
				$reservedQty = $item->getreserved_qty();
				$missingQty = $remaining_qty - $reservedQty;
			}
			else
			{
				$remaining_qty = $item->getQty();
				$parentItem = $item->getParentItem();
				if ($parentItem)
					$remaining_qty = $item->getQty() * $parentItem->getQty();
				//if quote mode, reserved qty is available qty
				$reservedQty = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($productId, $websiteId);
				if ($reservedQty > $remaining_qty)
					$reservedQty = $remaining_qty;
				$missingQty = $remaining_qty;
			}

			if ($remaining_qty > 0)
			{
				if ($reservedQty >= $remaining_qty)
					$ProductInformation .= '<font color="green">'.$item->getName().mage::helper('SalesOrderPlanning')->__(' reserved (').$reservedQty.')</font><br>';
				else
				{
					//remains product to reserve
					$productAvailabilityStatus = mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->getForOneProduct($productId);
					$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
					$product = $item->getProduct();

					//Check if product overrides subproducts qty
					if ($product->getoverride_subproducts_planning() == 1)
					{
						$ProductInformation .= $item->getName().' overrides childs ('.$productAvailabilityStatus->getpa_supply_delay().' days)<br>';
					}

					//if product manage stock
					if ($stockItem->getManageStock())
					{
						$allProductReserved = false;

						$ProductInformation .= '<font color="red">'.$item->getName().' : '.$missingQty.mage::helper('SalesOrderPlanning')->__(' missing').'</font>';

						//If product has parent and if this poarent overrides planning
						$parentItem = $item->getParentItem();
						$OverridePlanning = null;
						if ($parentItem != null)
						{
							$parentProductId = $parentItem->getproduct_id();
							if (isset($ParentProductsWhichOverridePlanning[$parentProductId]))
							{
								$OverridePlanning =  $beginingDate + $ParentProductsWhichOverridePlanning[$parentProductId] * 3600 * 24;
								$ProductInformation .= mage::helper('SalesOrderPlanning')->__(' (planning overrided) <br>');
							}
							else
								$ProductInformation .= mage::helper('SalesOrderPlanning')->__(' (Parent doesnt override) <br>');
						}
						else
							$ProductInformation .= mage::helper('SalesOrderPlanning')->__(' (no parent item) <br>');


						//If no parent or no planning override
						if ($OverridePlanning == null)
						{
							//calculate product supply date  - FIX : the begging date have to begin today, not from the moment the order has been placed else it is wrong
							$beginingDate = strtotime(date('Y-m-d'));
							$ProductSupplyDate = $this->getEstimatedDateForQty($item, $order, $remaining_qty, $beginingDate, $productAvailabilityStatus, $product);
							$ProductSupplyDateTimestamp = strtotime($ProductSupplyDate);
							$ProductInformation .= mage::helper('SalesOrderPlanning')->__(' (estimated date is : %s)<br>', $ProductSupplyDate);
							if (($ProductSupplyDate != null) && ($ProductSupplyDateTimestamp > $worstSupplyDate))
								$worstSupplyDate = $ProductSupplyDateTimestamp;
						}
						else
						{
							if ($OverridePlanning > $worstSupplyDate)
								$worstSupplyDate = $OverridePlanning;
						}
					}
					else
						$ProductInformation .= '<font color="green">'.$item->getName().mage::helper('SalesOrderPlanning')->__(' does not manage stock</font><br>');
				}
			}
			else
				$ProductInformation .= '<font color="green">'.$item->getName().mage::helper('SalesOrderPlanning')->__(' complete</font><br>');
		}

		//define values
		$fullstockDateTimeStamp = $worstSupplyDate;
		$fullstockComments = $ProductInformation;

		//if real fullstock date is set, use it
		if ($this->getpsop_real_fullstock_date() != null)
		{
			$fullstockDateTimeStamp = strtotime($this->getpsop_real_fullstock_date());
			$fullstockComments = mage::helper('SalesOrderPlanning')->__('From real fullstock date<br>');
			if ($fullstockDateTimeStamp < $beginingDate)
				$fullstockDateTimeStamp = $beginingDate;
		}

		//avoid holy day (if set)
		if (Mage::getStoreConfig('planning/fullstock/avoid_holy_days') == 1)
		{
			$daysToAdd = $this->DaysUntilNotHolyDay($fullstockDateTimeStamp,$order->getStoreId());
			if ($daysToAdd > 0)
			{
				$fullstockDateTimeStamp += 3600 * 24 * $daysToAdd;
				$fullstockComments .= mage::helper('SalesOrderPlanning')->__('add %s days to avoid holy day<br>', $daysToAdd);
			}
		}

		//add security (max date)
		$considerationDateTimestamp = strtotime($this->getConsiderationDate());
		$mode = Mage::getStoreConfig('planning/fullstock/maxdate_calculation_mode');
		$value = Mage::getStoreConfig('planning/fullstock/maxdate_calculation_value');
		$diff = $fullstockDateTimeStamp - $considerationDateTimestamp;
		$newDiff = 0;
		if ($value > 0)
		{
			switch ($mode)
			{
				case 'days':
					$newDiff += $diff + ($value * 3600 * 24);
					$fullstockComments .= mage::helper('SalesOrderPlanning')->__('add %s days to calculate max date<br>', $value);
					break;
				case 'percent':
					$newDiff += $diff * (1 + $value / 100);
					$fullstockComments .= mage::helper('SalesOrderPlanning')->__('add %s % to calculate max date<br>', $value);
					break;
			}
		}
		$maxDateTimestamp = $fullstockDateTimeStamp + $newDiff;//FIX the max date

		//store values
		$fullstockComments .= mage::helper('SalesOrderPlanning')->__('Full stock estimated to %s <br>', date('Y-m-d', $fullstockDateTimeStamp));
		$this->setpsop_fullstock_date(date('Y-m-d', $fullstockDateTimeStamp));
		$this->setpsop_fullstock_date_max(date('Y-m-d', $maxDateTimestamp));
		$this->setpsop_fullstock_comments($fullstockComments);
	}

	/**
	 * Return estimated date for 1 product
	 *
	 * @param unknown_type $orderItem
	 * @param unknown_type $order
	 * @param unknown_type $remaining_qty
	 * @param unknown_type $beginingDate
	 * @param unknown_type $productAvailabilityStatus
	 * @return unknown
	 */
	protected function getEstimatedDateForQty($orderItem, $order, $remaining_qty, $beginingDate, $productAvailabilityStatus, $product)
	{
		$value = null;

		//first, check if there is an expected purchase order for this product and if this order is prioritary
		if ($product->getsupply_date())
		{
			if (mage::helper('purchase/DeliveriesReservation')->orderIsPriorForPurchaseOrderDelivery($order, $product))
				$value = $product->getsupply_date();
		}

		//if no date, return date from product availability status
		if ($value == null)
			$value = $productAvailabilityStatus->getEstimatedDateForQty($remaining_qty, $beginingDate);

		return $value;
	}


	/**
	 * Set shipping information
	 *
	 */
	public function setShippingInformation($order, $quoteMode = false)
	{
		$shippingDateTimeStamp = null;
		$maxDateTimestamp = null;
		$shippingComments = null;

		//get shipment for order
		$shipmentDate = null;
		$shipments = $order->getShipmentsCollection();
		if ($shipments)
		{
			foreach ($shipments as $shipment)
			{
				if ($shipmentDate == null)
					$shipmentDate = $shipment->getCreatedAt();
			}

			if ($shipmentDate != null)
			{
				$shipmentDateForComment = Mage::helper('core')->formatTime($shipmentDate, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
				$shippingComments .= mage::helper('SalesOrderPlanning')->__('Order shipped on %s <br>', $shipmentDateForComment);
				$this->setpsop_shipping_date($shipmentDate);
				$this->setpsop_shipping_comments($shippingComments);
				return $this;
			}
		}

		//if no fullstock date, do not compute
		$fullstockDate = $this->getFullstockDate();
		$fullstockDateTimestamp = strtotime($fullstockDate);
		if ($fullstockDate == null)
		{
			$this->setpsop_shipping_date(null);
			$this->setpsop_shipping_date_max(null);
			$this->setpsop_shipping_comments('');
			return $this;
		}

		//add preparation duration
		$orderPreparationDuration = $this->getPreparationDurationForOrder($order, $quoteMode);

		//avoid holy day (if set)
		if (Mage::getStoreConfig('planning/shipping/avoid_holy_days') == 1)
		{
			$holidayHelper = mage::helper('SalesOrderPlanning/Holidays');
			$shippingDateTimeStamp = $holidayHelper->addDaysWithoutHolyDays(strtotime($fullstockDate), $orderPreparationDuration,$order->getStoreId());
			$shippingComments .= mage::helper('SalesOrderPlanning')->__('add %s days to prepare order and avoid holidays<br>', $orderPreparationDuration);
		}
		else
		{
			$shippingDateTimeStamp = strtotime($fullstockDate) + ($orderPreparationDuration * 3600 * 24);
			$shippingComments .= mage::helper('SalesOrderPlanning')->__('add %s days to prepare order<br>', $orderPreparationDuration);
		}

		//add security (max date)
		$mode = Mage::getStoreConfig('planning/shipping/maxdate_calculation_mode');
		$value = Mage::getStoreConfig('planning/shipping/maxdate_calculation_value');
		$diff = $orderPreparationDuration * 3600 * 24;
		$newDiff = 0;
		if ($value > 0)
		{
			switch ($mode)
			{
				case 'days':
					$newDiff += $diff + ($value * 3600 * 24);
					$shippingComments .= mage::helper('SalesOrderPlanning')->__('add %s days to calculate max date<br>', $value);
					break;
				case 'percent':
					$newDiff += $diff * (1 + $value / 100);
					$shippingComments .= mage::helper('SalesOrderPlanning')->__('add %s % to calculate max date<br>', $value);
					break;
			}
		}
		$maxDateTimestamp = strtotime($this->getpsop_fullstock_date_max()) + $newDiff;

		//avoid holy day for max date (if set)
		if (Mage::getStoreConfig('planning/shipping/avoid_holy_days') == 1)
		{
			$daysToAdd = $this->DaysUntilNotHolyDay($maxDateTimestamp,$order->getStoreId());
			if ($daysToAdd > 0)
			{
				$maxDateTimestamp += 3600 * 24 * $daysToAdd;
			}
		}


		//store values
		if ($shippingDateTimeStamp != null)
		{
			$shippingComments .= mage::helper('SalesOrderPlanning')->__('Shipping date estimated to %s <br>', date('Y-m-d', $shippingDateTimeStamp));
			$this->setpsop_shipping_date(date('Y-m-d', $shippingDateTimeStamp));
			$this->setpsop_shipping_date_max(date('Y-m-d', $maxDateTimestamp));
			$this->setpsop_shipping_comments($shippingComments);
		}

	}

	/**
	 * Set delivery information
	 *
	 * @param unknown_type $order
	 */
	public function setDeliveryInformation($order, $quoteMode = false)
	{
		$deliveryDateTimeStamp = null;
		$maxDateTimestamp = null;
		$deliveryComments = null;

		//if no shipping date, do not compute
		$shippingDate = $this->getShippingDate();
		$shippingDateTimestamp = strtotime($shippingDate);
		if ($shippingDate == null)
		{
			$this->setpsop_delivery_date(null);
			$this->setpsop_delivery_date_max(null);
			$this->setpsop_delivery_comments('');
			return $this;
		}

		//define shipping date
		if (!$quoteMode)
			$carrier = $order->getshipping_method();
		else
			$carrier = $order->getShippingAddress()->getShippingMethod();
		$country = '';
		if ($order->getShippingAddress() != null)
			$country = $order->getShippingAddress()->getcountry();
		$shippingDelay = mage::helper('SalesOrderPlanning/ShippingDelay')->getShippingDelayForCarrier($carrier, $country);

		//avoid holy day (if set)
		if (Mage::getStoreConfig('planning/delivery/avoid_holy_days') == 1)
		{
			$holidayHelper = mage::helper('SalesOrderPlanning/Holidays');
			$deliveryDateTimeStamp = $holidayHelper->addDaysWithoutHolyDays($shippingDateTimestamp, $shippingDelay,$order->getStoreId());
			$deliveryComments .= 'add '.$shippingDelay.' days for shipping delay with '.$carrier.' to '.$country.' and avoid holidays<br>';
		}
		else
		{
			$deliveryDateTimeStamp = $shippingDateTimestamp + ($shippingDelay * 3600 * 24);
			$deliveryComments .= 'add '.$shippingDelay.' days for shipping delay with '.$carrier.' to '.$country.'<br>';
		}

		//add security (max date)
		$newDiff = $this->calculateMaxDayDiff($deliveryDateTimeStamp, $shippingDateTimestamp, $deliveryComments);
		if(Mage::getStoreConfig('planning/delivery/maxdate_calculation_value')>0) {
			$maxDateTimestamp = strtotime($this->getpsop_shipping_date_max()) + $newDiff;
		}else{
			$maxDateTimestamp = $deliveryDateTimeStamp;//fix the max date < delivery time
		}


		//store values
		if ($deliveryDateTimeStamp != null)
		{
			$deliveryComments .= mage::helper('SalesOrderPlanning')->__('Delivery date estimated to %s <br>', date('Y-m-d', $deliveryDateTimeStamp));
			$this->setpsop_delivery_date(date('Y-m-d', $deliveryDateTimeStamp));
			$this->setpsop_delivery_date_max(date('Y-m-d', $maxDateTimestamp));
			$this->setpsop_delivery_comments($deliveryComments);
		}

	}

	public function calculateMaxDayDiff($deliveryDateTimeStamp, $shippingDateTimestamp, &$deliveryComments){
		//add security (max date)
		$mode = Mage::getStoreConfig('planning/delivery/maxdate_calculation_mode');
		$value = Mage::getStoreConfig('planning/delivery/maxdate_calculation_value');
		$diff = $deliveryDateTimeStamp - $shippingDateTimestamp;
		$newDiff = 0;
		if ($value > 0)
		{
			switch ($mode)
			{
				case 'days':
					$newDiff += $diff + ($value * 3600 * 24);
					$deliveryComments .= mage::helper('SalesOrderPlanning')->__('add %s days to calculate max date<br>', $value);
					break;
				case 'percent':
					$newDiff += $diff * (1 + $value / 100);
					$deliveryComments .= mage::helper('SalesOrderPlanning')->__('add %s % to calculate max date<br>', $value);
					break;
			}
		}
		return $newDiff;
	}


	/**
	 * Return progress for product reservation
	 *
	 */
	public function getProductsReservationProgressPercent($order)
	{
		try
		{
			$retour = 0;
			$productCount = 0;
			$reservedProductCount = 0;

			foreach($order->getAllItems() as $item)
			{
				$productId = $item->getproduct_id();
				if(!$productId)
					continue;
				$product = mage::getModel('catalog/product')->load($productId);
				if ($product->getStockItem()->getManageStock())
				{
					$productCount += $item->getqty_ordered();
					$reservedProductCount += $item->getreserved_qty();
				}
			}

			if ($productCount > 0)
			{
				$retour = (int)(($reservedProductCount * 100) / $productCount);
			}
		}
		catch (Exception $ex)
		{
			mage::logException(ex);
		}
		return $retour;

	}

	/*****************************************************************************************************************************
	 ******************************************************************************************************************************
	 ****************** TOOLS *****************************************************************************************************
	 ******************************************************************************************************************************
	 ******************************************************************************************************************************/

	private function getSupplyDelayForProduct($product)
	{
		die('deprecated, use product availability status');
		/*
		$value = $product->getdefault_supply_delay();
		if ($value == '')
			$value = mage::getStoreConfig('purchase/purchase_product/product_default_supply_delay');
		return $value;
		*/
	}

	/**
	 * Add day until date is not holy day
	 *
	 * @param float $dateTimestamp
	 * @param integer $storeId
	 */
	public function DaysUntilNotHolyDay($dateTimestamp,$storeId)
	{
		$nbDays = 0;

		$loop = true;
		$maxLoop = 1000;
		$loopCount = 0;
		$daysInSeconds = 24*3600;

		while ($loop)
		{
			$loopCount++;

			//avoid infinite loop
			if($loopCount>$maxLoop)
				break;

			if ($this->isHolyDay($dateTimestamp,$storeId))
			{
				$nbDays += 1;
				$dateTimestamp += $daysInSeconds;
			}
			else
				$loop = false;
		}

		return $nbDays;
	}

	/**
	 * Function to check if a date is holy day
	 *
	 *  @param float $dateTimestamp
	 * @param integer $storeId
	 */
	public function isHolyDay($dateTimestamp,$storeId)
	{
		return mage::helper('SalesOrderPlanning/Holidays')->isHolyDay($dateTimestamp,$storeId);
	}

	/**
	 * Return invoiced date for order
	 *
	 * @param model_order $order
	 */
	public function getOrderInvoicedDate($order)
	{
		$orderInvoicedDate = null;

		$firstInvoice = $order->getInvoiceCollection()->getFirstItem();

		if($firstInvoice->getId()>0){
			$orderInvoicedDate = $firstInvoice->getCreatedAtStoreDate();
			if(!$orderInvoicedDate) {
				$orderInvoicedDate = $firstInvoice->getCreatedAt();
			}
		}

		return $orderInvoicedDate;
	}

	/**
	 * Return delay for payment
	 *
	 * @param unknown_type $paymentMethod
	 * @return unknown
	 */
	public function getPaymentDelay($paymentMethod)
	{
		$paymentDelay = 0;

		//check if method belong to immediate ones
		$immediateMethods = Mage::getStoreConfig('planning/quote_options/immediate_payment_method');
		$pos = strpos($immediateMethods, $paymentMethod);

		if ($pos === false)
			$paymentDelay = Mage::getStoreConfig('planning/quote_options/delayed_payment_delay');

		return $paymentDelay;
	}

	/**
	 * Return preparation duration for order
	 *
	 * @param unknown_type $order
	 */
	public function getPreparationDurationForOrder($order, $quoteMode = false)
	{
		return Mage::getStoreConfig('planning/shipping/order_preparation_duration');
	}


	/**
	 * when saving, update supply needs for product (if concerned)
	 *
	 */
	protected function _afterSave()
	{
		parent::_afterSave();
	}

	public function copyAnnouncedDateFromOrder($order){
		$this->setpsop_anounced_date($order->getanounced_date());
		$this->setpsop_anounced_date_max($order->getanounced_date_max());
	}

	public function adjustPaymentDate($order){

		$psopRealPaymentDate = new Zend_Db_Expr('null');

		if (Mage::getStoreConfig('planning/consider/consider_order_on_paypment_validated') == 1) {
			if ($order->getpayment_validated() == 1)
				$psopRealPaymentDate = Mage::app()->getLocale()->date(date('Y-m-d H:i'), 'yyyy-MM-dd HH:mm')->toString('yyyy-MM-dd HH:mm');
		}

		$this->setpsop_real_payment_date($psopRealPaymentDate);
	}

}