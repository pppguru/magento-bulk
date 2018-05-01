<?php
/**
 * Rewrites core Mage_Catalog_Block_Product_View
 * Reason: getTierPrices funcion claculates save percentage incorrectly for tier prices
 * @author   Mohin - May 28, 2016
 */
class Bulksupplements_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_View
{    	
	public function getTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $prices = $product->getFormatedTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty'] * 1;

                $_productPrice = $product->getPrice();
                if ($_productPrice != $product->getFinalPrice()) {
                    $_productPrice = $product->getFinalPrice();
                }

                // Group price must be used for percent calculation if it is lower
                $groupPrice = $product->getGroupPrice();
                if ($_productPrice > $groupPrice) {
                    $_productPrice = $groupPrice;
                }

                if ($price['price'] <= $_productPrice) {
					//ceil makes 1.00005 to 2 which is wrong while calculating save percentage. We want to make 1.45 to 1 and 1.55 to 2. Mohin - May 28, 2016
					//$price['savePercent'] = ceil(100 - ((100 / $_productPrice) * $price['price']));
					$price['savePercent'] = round(100 - ((100 / $_productPrice) * $price['price']));

                    $tierPrice = Mage::app()->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['website_price'])
                    );
                    $price['formated_price'] = Mage::app()->getStore()->formatPrice($tierPrice);
                    $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(
                        Mage::app()->getStore()->convertPrice(
                            Mage::helper('tax')->getPrice($product, $price['website_price'], true)
                        )
                    );

                    if (Mage::helper('catalog')->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        $this->getPriceHtml($product);
                        $product->setPriceCalculation(true);

                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[] = $price;
                }
            }
        }

        return $res;
    }
}
