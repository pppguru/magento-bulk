<?php


/**
 * Class to calculate order weight
 *
 */
class MDN_Orderpreparation_Model_OrderWeightCalculation  extends Mage_Core_Model_Abstract
{
	const METHOD_ADD_NONE = 'ADD_NONE';
	const METHOD_ADD_FIX_WEIGHT = 'ADD_FIX_WEIGHT';
	const METHOD_ADD_FIX_WEIGHT_PER_PRODUCT = 'ADD_FIX_WEIGHT_PER_PRODUCT';
	const METHOD_ADD_PERCENT = 'ADD_PERCENT';
	
	public function _construct()
	{
		$this->_init('Orderpreparation/OrderWeightCalculation');
		parent::_construct();
	}
	
	/**
	 * Method to calculate order weight depending of configuration
	 *
	 * @param unknown_type $order
	 */
	public function calculateOrderWeight($products)
	{
		$retour = 0;
		
		//If calculation is enabled
		if (mage::getStoreConfig('orderpreparation/order_weight_calculation/enable'))
		{
			//compute bulk product weight
			for ($i=0;$i<count($products);$i++)
			{
				$productId = $products[$i]['product_id'];
				$product = mage::getModel('catalog/product')->load($productId);
				if ($product->getId())
				{
					$qty = $products[$i]['qty'];
					$retour += $product->getWeight() * $qty;
				}
			}
			
			//Add additional weight
			$methodValue = mage::getStoreConfig('orderpreparation/order_weight_calculation/additional_weight_value');
			switch (mage::getStoreConfig('orderpreparation/order_weight_calculation/additional_weight_method'))
			{
				case MDN_Orderpreparation_Model_OrderWeightCalculation::METHOD_ADD_FIX_WEIGHT:
					$retour += $methodValue;				
					break;
				case MDN_Orderpreparation_Model_OrderWeightCalculation::METHOD_ADD_FIX_WEIGHT_PER_PRODUCT:
					for ($i=0;$i<count($products);$i++)
					{
						$qty = $products[$i]['qty'];
						$retour += $qty * $methodValue;
					}				
					break;
				case MDN_Orderpreparation_Model_OrderWeightCalculation::METHOD_ADD_PERCENT:
					$retour += ($retour / 100) * $methodValue;
					break;
			}
		}
		
		
		return $retour;
	}
}