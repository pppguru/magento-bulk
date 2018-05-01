<?php

class Webshopapps_Wsacommon_Helper_Totals extends Mage_Core_Helper_Abstract {

    /**
     * Creates a new totals model and populates it with item total info
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param bool $useParent
     * @param bool $ignoreFreeItems
     * @param bool $cartFreeShipping
     * @return bool|Webshopapps_Wsacommon_Model_Totals
     */
    public function getTotals($item, $useParent = true, $ignoreFreeItems = true, $cartFreeShipping = false)
    {

        if (!$this->_isValidItem($item, $useParent, $ignoreFreeItems, $cartFreeShipping)) {
            return false;
        }

        if ($useParent && $item->getHasChildren() && $item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
            return $this->_processBundleParent($item, $cartFreeShipping);
        }

        if (!$useParent && $item->getParentItem() && $item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
            return $this->_processBundleChild($item, $cartFreeShipping);
        } else {

            $finalTotals = $this->_getStdWeightQtyTotals($item, $cartFreeShipping);

            self::setStandardPrices($item, $finalTotals);
        }

        return $finalTotals;
    }

    /**
     * Sets all variations of price on final totals for use with standard items. Not the parent bundle/configurable.
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param Webshopapps_Wsacommon_Model_Totals $finalTotals
     */
    protected function setStandardPrices($item, &$finalTotals) {

        $finalTotals->setBasePrice($finalTotals->getBasePrice() + $item->getBaseRowTotal());
        $finalTotals->setPrice($finalTotals->getPrice() + $item->getRowTotal());
        $finalTotals->setPriceInclTax($finalTotals->getPriceInclTax() + $item->getRowTotalInclTax());
        $finalTotals->setBasePriceInclTax($finalTotals->getBasePriceInclTax() + $item->getBaseRowTotalInclTax());

        $finalTotals->setDiscountedPrice($finalTotals->getDiscountedPrice() + ($item->getRowTotal() - $item->getDiscountAmount()));
        $finalTotals->setBaseDiscountedPrice($finalTotals->getBaseDiscountedPrice() + ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()));
        $finalTotals->setDiscountedPriceInclTax($finalTotals->getDiscountedPriceInclTax() + ($item->getRowTotalInclTax() - $item->getDiscountAmount()));
        $finalTotals->setBaseDiscountedPriceInclTax($finalTotals->getBaseDiscountedPriceInclTax() + ($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount()));
    }

    /**
     * Gets the item information for standard items or child items of configurable products
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param bool $cartFreeShipping
     * @return Webshopapps_Wsacommon_Model_Totals
     */
    protected function _getStdWeightQtyTotals($item, $cartFreeShipping) {
        $finalTotals = new Webshopapps_Wsacommon_Model_Totals();

        $addressWeight=0;
        $addressQty=0;
        $freeMethodWeight=0;

        if ($item->getHasChildren() && ($item->isShipSeparately() ||
                $item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)) {
            foreach ($item->getChildren() as $child) {
                if ($child->getProduct()->isVirtual()) {
                    continue;
                }
                $addressQty += $item->getQty()*$child->getQty();

                $itemWeight = $child->getWeight();
                $itemQty    = $child->getTotalQty();
                $rowWeight  = $itemWeight*$itemQty;
                if ($cartFreeShipping || $child->getFreeShipping()===true) {
                    $rowWeight = 0;
                } elseif (is_numeric($child->getFreeShipping())) {
                    $freeQty = $child->getFreeShipping();
                    if ($itemQty>$freeQty) {
                        $rowWeight = $itemWeight*($itemQty-$freeQty);
                    } else {
                        $rowWeight = 0;
                    }
                }
                $freeMethodWeight += $rowWeight;
                $addressWeight+= $rowWeight;
            }
        } else {
            $addressQty += $item->getQty();
            $itemWeight = $item->getWeight();
            $rowWeight  = $itemWeight*$item->getQty();
            $addressWeight+= $rowWeight;
            if ($cartFreeShipping || $item->getFreeShipping()===true) {
                $rowWeight = 0;
            } elseif (is_numeric($item->getFreeShipping())) {
                $freeQty = $item->getFreeShipping();
                if ($item->getQty()>$freeQty) {
                    $rowWeight = $itemWeight*($item->getQty()-$freeQty);
                } else {
                    $rowWeight = 0;
                }
            }
            $freeMethodWeight+= $rowWeight;
        }

        $finalTotals->setWeight($addressWeight);
        $finalTotals->setFreeMethodWeight($freeMethodWeight);
        $finalTotals->setQty($addressQty);

        return $finalTotals;
    }


    /**
     * Gatheres total info for children of a bundle product
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param Boolean $cartFreeShipping
     * @return Webshopapps_Wsacommon_Model_Totals
     */
    protected function _processBundleChild($item, $cartFreeShipping) {

        $finalTotals = new Webshopapps_Wsacommon_Model_Totals();

        $basePrice = 0;
        $storePrice =  0;
        $basePriceInclTax = 0;
        $priceInclTax = 0;

        $parentProduct = $item->getParentItem()->getProduct();
        $parentQty = $item->getParentItem()->getQty();

        if ($parentProduct->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {

            self::setBundleParentPrice($item, $finalTotals, false);

            if ($parentProduct->hasCustomOptions()) {
                $customOption = $parentProduct->getCustomOption('bundle_selection_ids');
                $selectionIds = unserialize($customOption->getValue());
                $selections = $parentProduct->getTypeInstance(true)->getSelectionsByIds($selectionIds, $parentProduct);
                if (method_exists($selections,'addTierPriceData')) {
                    $selections->addTierPriceData();
                }
                foreach ($selections->getItems() as $selection) {
                    if ($selection->getProductId()== $item->getProductId()) {
                        //Looks like Magento isn't multiplying the child item price by the qty, Confirmed in 1.6-1.7
                        $basePrice = ($item->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                                    $parentProduct, $item->getParentItem()->getQty(),
                                    $selection, $finalTotals->getQty()*$item->getParentItem()->getQty(), $item->getQty()) * $item->getQty()) * $parentQty;

                        //Price from here is always base. Convert to store to stay consistent unless flag $useBase is set.
                        $storePrice =  Mage::helper('directory')->currencyConvert($basePrice,
                                                                                  Mage::app()->getStore()->getBaseCurrencyCode(),
                                                                                  Mage::app()->getStore()->getCurrentCurrencyCode());


                        $calculator = Mage::helper('tax')->getCalculator();
                        $taxRequest = $calculator->getRateOriginRequest();
                        $taxRequest->setProductClassId($parentProduct->getTaxClassId());
                        $taxPercentage = $calculator->getRate($taxRequest);

                        $basePriceInclTax = ($basePrice + round(($taxPercentage/100) * $basePrice,2));;
                        $priceInclTax = round(Mage::helper('directory')->currencyConvert($basePriceInclTax,
                                                                                         Mage::app()->getStore()->getBaseCurrencyCode(), Mage::app()->getStore()->getCurrentCurrencyCode()),2);
                    }
                }
            }
        } else {
            $basePrice = $item->getBaseRowTotal();
            $storePrice =  $item->getRowTotal();
            $basePriceInclTax = $basePrice+$item->getBaseTaxAmount();
            $priceInclTax = $storePrice+$item->getTaxAmount();
        }

        if(!self::setBundleParentWeight($item, $cartFreeShipping, $finalTotals)) {
            $weight = $item->getWeight();
            $rowWeight = $weight * $parentQty;

            $finalTotals->setWeight($rowWeight);
            $finalTotals->setFreeMethodWeight($rowWeight);
        }

        $finalTotals->setBasePrice($finalTotals->getBasePrice() + $basePrice);
        $finalTotals->setPrice($finalTotals->getPrice() + $storePrice);
        $finalTotals->setPriceInclTax($finalTotals->getPriceInclTax() + $priceInclTax);
        $finalTotals->setBasePriceInclTax($finalTotals->getBasePriceInclTax() + $basePriceInclTax);

        //TODO - This is just here to make it work. Need to add discount support
        $finalTotals->setBaseDiscountedPrice($finalTotals->getBasePrice() - $item->getDiscountAmount());
        $finalTotals->setDiscountedPrice($finalTotals->getPrice() - $item->getDiscountAmount());
        $finalTotals->setDiscountedPriceInclTax($finalTotals->getPriceInclTax() - $item->getDiscountAmount());
        $finalTotals->setBaseDiscountedPriceInclTax($finalTotals->getBasePriceInclTax() - $item->getDiscountAmount());

        $finalTotals->setWeight($finalTotals->getWeight()*$item->getParentItem()->getQty());
        //  $finalTotals->setFreeMethodWeight($freeMethodWeight);  // TODO Not Present!

        $finalTotals->setQty($item->getParentItem()->getQty());

        return $finalTotals;
    }

    /**
     * Gets the fixed price of the parent product divided by the qty
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param Webshopapps_Wsacommon_Model_Totals $finalTotals
     * @param bool $isParent
     */
    private function setBundleParentPrice($item, &$finalTotals, $isParent = true) {

        $parentQty = $isParent ? $item->getQty() : $item->getParentItem()->getQty();
        $childQty = $isParent ? 1 : count($item->getParentItem()->getChildren());

        $parentItem = $isParent ? $item : $item->getParentItem();

        $basePrice = $parentItem->getProduct()->getPrice() * $parentQty;
        $storePrice = Mage::helper('directory')->currencyConvert($basePrice,
                                                                 Mage::app()->getStore()->getBaseCurrencyCode(), Mage::app()->getStore()->getCurrentCurrencyCode());

        $calculator = Mage::helper('tax')->getCalculator();
        $taxRequest = $calculator->getRateOriginRequest();
        $taxRequest->setProductClassId($parentItem->getProduct()->getTaxClassId());
        $taxPercentage = $calculator->getRate($taxRequest);

        $baseTaxAmount = round((($taxPercentage/100) * $basePrice) / $childQty, 2);
        $storeTaxAmount = round(Mage::helper('directory')->currencyConvert($baseTaxAmount,
                                                                           Mage::app()->getStore()->getBaseCurrencyCode(), Mage::app()->getStore()->getCurrentCurrencyCode()), 2);


        //todo finish - do this for others below. its neater
        $basePrice = $isParent ? $basePrice : $basePrice / $childQty;
        $storePrice = $isParent ? $storePrice : $storePrice / $childQty;

        $finalTotals->setBasePrice($finalTotals->getBasePrice() + $basePrice);
        $finalTotals->setPrice($finalTotals->getPrice() + $storePrice);
        $finalTotals->setPriceInclTax($finalTotals->getPriceInclTax() + ($storePrice + $storeTaxAmount));
        $finalTotals->setBasePriceInclTax($finalTotals->getBasePriceInclTax() + ($basePrice + $baseTaxAmount));

        //TODO - This is just here to make it work. Add discount support
        $finalTotals->setDiscountedPrice($finalTotals->getBasePrice() + $basePrice);
        $finalTotals->setBaseDiscountedPrice($finalTotals->getPrice() + $storePrice);
        $finalTotals->setDiscountedPriceInclTax($finalTotals->getPriceInclTax() + ($storePrice + $storeTaxAmount));
        $finalTotals->setBaseDiscountedPriceInclTax($finalTotals->getBasePriceInclTax() + ($basePrice + $baseTaxAmount));
    }

    /**
     * Retrieves the fixed weight set on the parent item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param bool $cartFreeShipping is the cart marked as free shipping
     * @param Webshopapps_Wsacommon_Model_Totals $finalTotals the final totals model instance
     * @param bool $isParent is the $item the parent item or a child
     * @return bool success
     */
    private function setBundleParentWeight($item, $cartFreeShipping, &$finalTotals, $isParent = true) {

        $parentItem = $isParent ? $item : $item->getParentItem();

        if($parentItem->getProduct()->getWeightType()) {
            $itemWeight = $parentItem->getProduct()->getWeight();
            $rowWeight  = $itemWeight*$parentItem->getQty();
            $addressWeight = $rowWeight;

            if ($cartFreeShipping || $item->getFreeShipping()===true) {
                $rowWeight = 0;
            } elseif (is_numeric($item->getFreeShipping())) {
                $freeQty = $item->getFreeShipping();
                if ($item->getQty()>$freeQty) {
                    $rowWeight = $itemWeight*($item->getQty()-$freeQty);
                } else {
                    $rowWeight = 0;
                }
            }
            $finalTotals->setWeight($addressWeight);
            $finalTotals->setFreeMethodWeight($rowWeight);

            return true;
        } else {
            return false;
        }
    }


    /**
     * Finds total information for a bundle item if applicable.
     * Only applicable when bundle is set to use fixed prices or weights on product listing.
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param $cartFreeShipping
     * @return Webshopapps_Wsacommon_Model_Totals
     */
    protected function _processBundleParent($item, $cartFreeShipping) {

        $finalTotals = new Webshopapps_Wsacommon_Model_Totals();

        //Add on the fixed initial parent price and/or weight. Children are calculated separately.
        if ($item->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
            self::setStandardPrices($item, $finalTotals);
        } else {
            foreach($item->getChildren() as $child) {
                self::setStandardPrices($child, $finalTotals);
            }
        }

        if(!self::setBundleParentWeight($item, $cartFreeShipping, $finalTotals)) {
            $finalTotals->setWeight($item->getRowWeight());
            $finalTotals->setFreeMethodWeight($item->getRowWeight());
        }

        $finalTotals->setQty($item->getQty());

        return $finalTotals;
    }

    /**
     * Finds if the item is usable and applicable to current config
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param $useParent
     * @param $ignoreFreeItems
     * @param $cartFreeShipping
     * @return bool
     */
    private function _isValidItem($item, $useParent, $ignoreFreeItems, $cartFreeShipping) {

        if (!is_object($item))
        {
            Mage::helper('wsalogger/log')->postCritical('wsacommon','Fatal Error','Item/Product is Malformed');
            return false;
        }

        /**
         * Skip if this item is virtual
         **/

        if ($item->getProduct()->isVirtual()) {
            return false;
        }

        if ($ignoreFreeItems && ($item->getFreeShipping() || $cartFreeShipping)) {
            Mage::helper('wsalogger/log')->postInfo('wsacommon',
                                                    'Item Skipped - '.$item->getId(),
                                                    'Item/Cart has free shipping & Extension set to ignore free items.');
            return false;
        }

        /**
         * Children weight we calculate for parent
         */

        if ($item->getParentItem() && ( ($item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $useParent)
                || $item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE  )) {
            return false;
        }

        if (!$useParent && $item->getHasChildren() && $item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return false;
        }

        return true;

    }
}