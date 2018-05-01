<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Block_Rewrite_Checkout_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
    public function getEstimateRates()
    {
        $rates = parent::getEstimateRates();

        // checking methods visibility for customer groups
        foreach ($rates as $methodCode => $method)
        {
            if (!Mage::helper('ammethods')->canUseMethod($methodCode, 'shipping'))
            {
                unset($rates[$methodCode]);
            }
        }
        
        return $rates;
    }
}