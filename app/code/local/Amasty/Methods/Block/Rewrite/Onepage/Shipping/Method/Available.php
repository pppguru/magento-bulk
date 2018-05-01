<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */
class Amasty_Methods_Block_Rewrite_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();

            $groups = $this->getAddress()->getGroupedAllShippingRates();
            
            // checking methods visibility for customer groups
            foreach ($groups as $methodCode => $method)
            {
                if (!Mage::helper('ammethods')->canUseMethod($methodCode, 'shipping'))
                {
                    unset($groups[$methodCode]);
                }
            }

            return $this->_rates = $groups;
        }

        return $this->_rates;
    }
}