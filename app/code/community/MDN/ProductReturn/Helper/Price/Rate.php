<?php

/**
 * Class MDN_ProductReturn_Helper_Price_Rate
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_Price_Rate extends Mage_Core_Helper_Abstract {

    /**
     * @param string $baseCurrencyCode
     * @param string $targetCurrencyCode
     * @return float $rate
     */
    public function getRate($baseCurrencyCode, $targetCurrencyCode){

        $rate = 1;

        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $currencyRates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));

        if(isset($currencyRates[$targetCurrencyCode])) {

            $rate = $currencyRates[$targetCurrencyCode];

        }


        return $rate;

    }

}