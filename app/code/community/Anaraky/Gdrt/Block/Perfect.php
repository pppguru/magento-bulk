<?php
/**********************************************************
* @author : Matthew

<script type="text/javascript">
//Perfect Audience Tracking Code
(function() {
	window._pa = window._pa || {};
	// _pa.orderId = "myOrderId"; // OPTIONAL: attach unique conversion identifier to conversions
	// _pa.revenue = "19.99"; // OPTIONAL: attach dynamic purchase values to conversions
	// _pa.productId = "myProductId"; // OPTIONAL: Include product ID for use with dynamic ads
	var pa = document.createElement('script'); pa.type = 'text/javascript'; pa.async = true;
	pa.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + "//tag.perfectaudience.com/serve/531d9f7a6fe1883956000069.js";
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(pa, s);
})();
</script>
***********************************************************/

class Anaraky_Gdrt_Block_Perfect extends Mage_Core_Block_Abstract {

	private $_storeId = 0;
	private $_pid = false;
	private $_pid_prefix = "";
	private $_pid_prefix_ofcp = 0;
	private $_pid_ending = "";
	private $_pid_ending_ofcp = 0;

	private function getEcommProdid($product)
	{
		$ecomm_prodid = (string)($this->_pid ? $product->getId() : $product->getSku());
		$ofcp = false;
		if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
			$product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
		{
			$ofcp = true;
		}

		if (!empty($this->_pid_prefix) && (($this->_pid_prefix_ofcp === 1 && $ofcp) ||
			$this->_pid_prefix_ofcp === 0))
		{
				$ecomm_prodid = $this->_pid_prefix . $ecomm_prodid;
		}

		if (!empty($this->_pid_ending) && (($this->_pid_ending_ofcp === 1 && $ofcp) ||
			$this->_pid_ending_ofcp === 0))
		{
				$ecomm_prodid .= $this->_pid_ending;
		}

		return $ecomm_prodid;
	}

	private function getParams()
	{
		if ((int)Mage::getStoreConfig('gdrt/general/gdrt_product_id', $this->_storeId) === 0)
			$this->_pid = true;

		$this->_pid_prefix = Mage::getStoreConfig('gdrt/general/gdrt_product_id_prefix', $this->_storeId);
		$this->_pid_prefix_ofcp = (int)Mage::getStoreConfig('gdrt/general/gdrt_product_id_prefix_ofcp', $this->_storeId);
		$this->_pid_ending = Mage::getStoreConfig('gdrt/general/gdrt_product_id_ending', $this->_storeId);
		$this->_pid_ending_ofcp = (int)Mage::getStoreConfig('gdrt/general/gdrt_product_id_ending_ofcp', $this->_storeId);

		$inclTax = false;
		if ((int)Mage::getStoreConfig('gdrt/general/gdrt_tax', $this->_storeId) === 1)
			$inclTax = true;

		$type = $this->getData('pageType');
		$params = array();
		switch ($type) {
			case 'product':
				$product = Mage::registry('current_product');
				$totalvalue = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $inclTax);

				$params = array(
					'_pa.revenue' =>  (float)number_format($totalvalue, '2', '.', ''),
					'_pa.productId' => $this->getEcommProdid($product)
				);
				unset($product);
				break;

			case 'cart':
				$cart = Mage::getSingleton('checkout/session')->getQuote();
				$items = $cart->getAllVisibleItems();

				if (count($items) > 0) {
					$data  = array();
					$totalvalue = 0;
					foreach ($items as $item)
					{
						$data[0][] = $this->getEcommProdid($item);
//						$data[1][] = (int)$item->getQty();
						$totalvalue += $inclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
					}

					$params = array(
						'_pa.revenue' => (float)number_format($totalvalue, '2', '.', ''),
						'_pa.productId' => $data[0]
					);
				}
				else
					$params = array( 'ecomm_pagetype' => 'siteview' );

				unset($cart, $items, $item, $data);
				break;

			case 'purchase':
				$order = Mage::getModel('sales/order')->loadByIncrementId(
								Mage::getSingleton('checkout/session')
											->getLastRealOrderId());

				$_orderNumber = $order->getIncrementId();
				$data  = array();
				$totalvalue = 0;
				$items = $order->getAllItems();

				foreach ($items as $item)
				{
					$data[0][] = $this->getEcommProdid($item);
//					$data[1][] = (int)$item->getQtyToInvoice();
					$totalvalue += $inclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
				}

				$params = array(
					'_pa.orderId' => $_orderNumber,
					'_pa.revenue' => (float)number_format($totalvalue, '2', '.', ''),
					'_pa.productId' => $data[0]
				);
				unset($order, $items, $item);
				break;

			default:
				break;
		}

		return $params;
	}

	private function paramsToJS($params)
	{
		$result = array();

		foreach ($params as $key => $value)
		{
			if (is_array($value) && count($value) == 1)
				$value = $value[0];

			if (is_array($value))
			{
				if (is_string($value[0]))
					$value = "'" . '"' . implode('","', $value) . '"' . "'";
				else
					$value = "'" . implode(',', $value) . "'";
			}
			else
				$value = '"' . $value . '"';
//			elseif (is_string($value))


			$result[] = $key . ' = ' . $value;
		}
		if ($result)
			return PHP_EOL . "\t" . implode(';' . PHP_EOL . "\t", $result) . ";" . PHP_EOL;

		return "";
	}

	private function paramsToURL($params)
	{
		$result = array();

		foreach ($params as $key => $value)
		{
			if (is_array($value))
				$value = implode(',', $value);

			$result[] = $key . '=' . $value;
		}

		return urlencode(implode(';', $result));
	}

	private function paramsToDebug($params)
	{
		$result = '';

		foreach ($params as $key => $value)
		{
			if (is_array($value) && count($value) == 1)
				$value = $value[0];

			if (is_array($value))
			{
				if (is_string($value[0]))
					$value = '["' . implode('","', $value) . '"]';
				else
					$value = '[' . implode(',', $value) . ']';
			}
			elseif (is_string($value))
				$value = '"' . $value . '"';

			$result .= '<tr>' .
				'           <td style="text-align:right;font-weight:bold;">' . $key . ': &nbsp;</td>' .
				'           <td style="text-align:left;"> ' . $value . '</td>' .
				'        </tr>';
		}

		return $result;
	}

	protected function _toHtml()
	{
		$gcParams = $this->getParams();

		$s = PHP_EOL .
			'<script type="text/javascript">' . PHP_EOL .
			'/* <![CDATA[ */' . PHP_EOL .
//            '(function() {' . PHP_EOL .
			'jQuery(window).load(function($) {' . PHP_EOL .
			'window._pa = window._pa || {};' . PHP_EOL .
			$this->paramsToJS($gcParams) .
			'var pa = document.createElement("script"); pa.type = "text/javascript"; pa.async = true;' . PHP_EOL .
			'pa.src = ("https:" == document.location.protocol ? "https:" : "http:") + "//tag.perfectaudience.com/serve/531d9f7a6fe1883956000069.js";' . PHP_EOL .
			'var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(pa, s);' . PHP_EOL .
			'});' . PHP_EOL .
//			'})();' . PHP_EOL .
			'/* ]]> */' . PHP_EOL .
			'</script>';

		return $s;
	}
}
