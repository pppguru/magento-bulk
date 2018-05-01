<?php

/* WSA Common
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsacommon
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

class Webshopapps_Wsacommon_Helper_Shipping extends Mage_Core_Helper_Abstract
{
    public static function getVirtualItemTotals($item, &$weight, &$qty, &$price, $useParent = true,
                                                $ignoreFreeItems = true, &$itemGroup = array(),
                                                $useDiscountValue = false, $cartFreeShipping = false, $useBase = false,
                                                $useTax = false, $includeVirtual = false)
    {

        $addressWeight = 0;
        $addressQty = 0;
        $freeMethodWeight = 0;
        $itemGroup[] = $item;
        $applyShipping = Mage::getModel('catalog/product')->load($item->getProduct()->getId())->getApplyShipping();
        $downloadShipping = Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Downloadshipping');
        $hasCustomOptions = 0;
        if ($downloadShipping) {
            $hasCustomOptions = Mage::helper('downloadshipping')->hasCustomOptions($item);
        }

        if (!$downloadShipping && $item->getProduct()->isVirtual() && !$includeVirtual) {

            return false;
        }

        if ($ignoreFreeItems && $item->getFreeShipping()) {
            return false;
        }

        /*
         * Children weight we calculate for parent
        */
        if ($item->getParentItem() &&
            (($item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $useParent)
                || $item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)) {
            return false;
        }

        if (!$useParent && $item->getHasChildren() && $item->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return false;
        }

        if ($item->getHasChildren() && $item->isShipSeparately()) {

            foreach ($item->getChildren() as $child) {
                $itemGroup[] = $item;
                if ($downloadShipping) {
                    if ($child->getProduct()->isVirtual() && !$applyShipping || !$hasCustomOptions) {
                        continue;
                    }
                }

                $addressQty += $item->getQty() * $child->getQty();

                if (!$item->getProduct()->getWeightType()) {
                    $itemWeight = $child->getWeight();
                    $itemQty = $child->getTotalQty();
                    $rowWeight = $itemWeight * $itemQty;
                    if ($cartFreeShipping || $child->getFreeShipping() === true) {
                        $rowWeight = 0;
                    } elseif (is_numeric($child->getFreeShipping())) {
                        $freeQty = $child->getFreeShipping();
                        if ($itemQty > $freeQty) {
                            $rowWeight = $itemWeight * ($itemQty - $freeQty);
                        } else {
                            $rowWeight = 0;
                        }
                    }
                    $freeMethodWeight += $rowWeight;
                }
            }
            if ($item->getProduct()->getWeightType()) {
                $itemWeight = $item->getWeight();
                $rowWeight = $itemWeight * $item->getQty();
                $addressWeight += $rowWeight;
                if ($cartFreeShipping || $item->getFreeShipping() === true) {
                    $rowWeight = 0;
                } elseif (is_numeric($item->getFreeShipping())) {
                    $freeQty = $item->getFreeShipping();
                    if ($item->getQty() > $freeQty) {
                        $rowWeight = $itemWeight * ($item->getQty() - $freeQty);
                    } else {
                        $rowWeight = 0;
                    }
                }
                $freeMethodWeight += $rowWeight;
            }
        } else {
            if ($downloadShipping || $includeVirtual) {

                if (!$item->getProduct()->isVirtual() || $item->getProduct()->isVirtual() && $applyShipping
                    || $hasCustomOptions || $includeVirtual) {

                    $addressQty += $item->getQty();
                } else {
                    return false;
                }
            }
            $itemWeight = $item->getWeight();
            $rowWeight = $itemWeight * $item->getQty();
            $addressWeight += $rowWeight;
            if ($cartFreeShipping || $item->getFreeShipping() === true) {
                $rowWeight = 0;
            } elseif (is_numeric($item->getFreeShipping())) {
                $freeQty = $item->getFreeShipping();
                if ($item->getQty() > $freeQty) {
                    $rowWeight = $itemWeight * ($item->getQty() - $freeQty);
                } else {
                    $rowWeight = 0;
                }
            }
            $freeMethodWeight += $rowWeight;
        }

        if (!$useParent && $item->getParentItem() &&
            $item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $weight = $addressWeight * $item->getParentItem()->getQty();
            $qty = $addressQty * $item->getParentItem()->getQty();
            $parentProduct = $item->getParentItem()->getProduct();
            !$useBase ? $finalPrice = $item->getRowTotal() : $finalPrice = $item->getBaseRowTotal();
            $useTax && $useBase ? $finalPrice += $item->getBaseTaxAmount() : false;
            $useTax && !$useBase ? $finalPrice += $item->getTaxAmount() : false;

            if ($parentProduct->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                if ($parentProduct->hasCustomOptions()) {
                    $customOption = $parentProduct->getCustomOption('bundle_option_ids');
                    $customOption = $parentProduct->getCustomOption('bundle_selection_ids');
                    $selectionIds = unserialize($customOption->getValue());
                    $selections = $parentProduct->getTypeInstance(true)->getSelectionsByIds($selectionIds, $parentProduct);
                    if (method_exists($selections, 'addTierPriceData')) {
                        $selections->addTierPriceData();
                    }
                    foreach ($selections->getItems() as $selection) {
                        if ($selection->getProductId() == $item->getProductId()) {
                            $finalPrice = $item
                                ->getParentItem()
                                ->getProduct()
                                ->getPriceModel()
                                ->getChildFinalPrice($parentProduct,
                                                     $item->getParentItem()->getQty(),
                                                     $selection,
                                                     $qty,
                                                     $item->getQty()
                                );
                            //Price from here is always base. Convert to store to stay consistent unless flag $useBase is set.
                            !$useBase ? $finalPrice = Mage::helper('directory')->currencyConvert($finalPrice, Mage::app()->getStore()->getBaseCurrencyCode(), Mage::app()->getStore()->getCurrentCurrencyCode()) : '';
                        }
                    }
                }
            }
            $price = $finalPrice;
        } else {
            $weight = $addressWeight;
            $qty = $addressQty;
            !$useBase ? $price = $item->getRowTotal() : $price = $item->getBaseRowTotal();
            $useTax && !$useBase ? $price += ($item->getRowTotalInclTax() - $item->getRowTotal()) : false;
            $useTax && $useBase ? $price += ($item->getBaseRowTotalInclTax() - $item->getBaseRowTotal()) : false;
        }

        if ($useDiscountValue) {
            !$useBase ? $price -= $item->getDiscountAmount() : $price -= $item->getBaseDiscountAmount();;
        }

        return true;
    }

    /**
     * Assigns totals values using variables passed in by reference.
     *
     * @deprecated This is only included for backward compatibility. Should now call getItemInclFreeTotals()
     * @param Mage_Sales_Model_Quote_Item $item
     * @param int                         $weight
     * @param int                         $qty
     * @param int                         $price
     * @param bool                        $useParent
     * @param bool                        $ignoreFreeItems
     * @param array                       $itemGroup
     * @param bool                        $useDiscountValue
     * @param bool                        $cartFreeShipping
     * @param bool                        $useBase
     * @param bool                        $useTax
     * @return bool
     */
    public static function getItemTotals($item,
                                         &$weight,
                                         &$qty,
                                         &$price,
                                         $useParent = true,
                                         $ignoreFreeItems = true,
                                         &$itemGroup = array(),
                                         $useDiscountValue = false,
                                         $cartFreeShipping = false,
                                         $useBase = false,
                                         $useTax = false)
    {
        $freeMethodWeight = 0;

        return self::getItemInclFreeTotals($item,
                                           $weight,
                                           $qty,
                                           $price,
                                           $freeMethodWeight,
                                           $useParent,
                                           $ignoreFreeItems,
                                           $itemGroup,
                                           $useDiscountValue,
                                           $cartFreeShipping,
                                           $useBase,
                                           $useTax
        );
    }

    /**
     * Assigns totals values using variables passed in by reference.
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param int                         $weight
     * @param int                         $qty
     * @param int                         $price
     * @param                             $freeMethodWeight
     * @param bool                        $useParent
     * @param bool                        $ignoreFreeItems
     * @param array                       $itemGroup
     * @param bool                        $useDiscountValue
     * @param bool                        $cartFreeShipping
     * @param bool                        $useBase
     * @param bool                        $useTax
     * @param int                         $basePriceInclTax
     * @return bool
     */
    public static function getItemInclFreeTotals($item,
                                                 &$weight,
                                                 &$qty,
                                                 &$price,
                                                 &$freeMethodWeight,
                                                 $useParent = true,
                                                 $ignoreFreeItems = true,
                                                 &$itemGroup = array(),
                                                 $useDiscountValue = false,
                                                 $cartFreeShipping = false,
                                                 $useBase = false,
                                                 $useTax = false,
                                                 &$basePriceInclTax = 0)
    {
        $adminOrder = false;

        /**
         * if order is placed in admin and we're using the sales_order event qty isn't set.
         * DIMSHIP-143
         */
        if ($item->getQtyOrdered() && !$item->getQty()) {
            $item->setQty($item->getQtyOrdered());
            $adminOrder = true;
        }

        $totals = Mage::helper('wsacommon/totals')->getTotals($item, $useParent, $ignoreFreeItems, $cartFreeShipping);

        if($adminOrder) {
            self::removeQty($item);
        }

        if (!$totals) {
            return false;
        } else {
            $weight = $totals->getWeight();
            $qty = $totals->getQty();

            if (!$useDiscountValue) {
                $useTax && !$useBase ? $price = $totals->getPriceInclTax() : false;
                $useTax && $useBase ? $price = $totals->getBasePriceInclTax() : false;
                !$useTax && !$useBase ? $price = $totals->getPrice() : false;
                !$useTax && $useBase ? $price = $totals->getBasePrice() : false;
            } else {
                $useTax && !$useBase ? $price = $totals->getDiscountedPriceInclTax() : false;
                $useTax && $useBase ? $price = $totals->getBaseDiscountedPriceInclTax() : false;
                !$useTax && !$useBase ? $price = $totals->getDiscountedPrice() : false;
                !$useTax && $useBase ? $price = $totals->getBaseDiscountedPrice() : false;
            }

            $freeMethodWeight = $totals->getFreeMethodWeight();

            self::processItemGroup($itemGroup, $item);

            return true;
        }
    }

    /**
     * Adds all the items to an array including any child items.
     * No need to check if item is valid. This is called after totals which already does the validation
     *
     * @param Array                       $itemGroup - The array which is passed back by reference
     * @param Mage_Sales_Model_Quote_Item $item      - The item to process
     */
    private static function processItemGroup(&$itemGroup, $item)
    {

        if ($item->getHasChildren() && $item->isShipSeparately()) {
            foreach ($item->getChildren() as $child) {
                $itemGroup[] = $child;
            }
        } else {
            $itemGroup[] = $item;
        }
    }

    /**
     * Remove the Qty property if the item is in admin scope. Wasn't there originally, don't want to impact anything.
     *
     * @param $item
     */
    private static function removeQty(&$item)
    {
        $item->unsetData('qty');
    }

    public static function updateStatus($session, $numRows)
    {
        if ($numRows < 1) {
            $session->addError(Mage::helper('adminhtml')->__($numRows . ' rows have been imported. See <a href="http://support.webshopapps.com/blog/2014/10/27/troubleshooting-guide-2/">knowledge base article for help</a>'));
        } else {
            $session->addSuccess(Mage::helper('adminhtml')->__($numRows . ' rows have been imported.'));
        }
    }

    /**
     * DO NOT USE THIS - See Helper/Data.php in Freight Common
     *
     * @deprecated May 2013
     * @return bool
     */
    public static function hasFreightCarrierEnabled()
    {
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Yrcfreight', 'carriers/yrcfreight/active') || Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsaupsfreight', 'carriers/wsaupsfreight/active') || Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Rlfreight', 'carriers/rlfreight/active') || Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Rlfreight', 'carriers/newgistics/active') || Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsafedexfreight', 'carriers/wsafedexfreight/active')) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves enabled freight carriers. Currently only returns one
     * DO NOT USE THIS - See Helper/Data.php in Freight Common
     *
     * @deprecated May 2013
     * @return string
     */
    public static function getFreightCarriers()
    {
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Yrcfreight', 'carriers/yrcfreight/active')) {
            return 'yrcfreight';
        }

        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsaupsfreight', 'carriers/wsaupsfreight/active')) {
            return 'wsaupsfreight';
        }

        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Rlfreight', 'carriers/rlfreight/active')) {
            return 'rlfreight';
        }

        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsafedexfreight', 'carriers/wsafedexfreight/active')) {
            return 'wsafedexfreight';
        }

        return '';
    }

    /**
     * Method to save a backup copy of the CSV file to the file system.
     *
     * @param String $file      - CSV file to be saved.
     * @param String $fileName  - What to call the file including extension.
     * @param string $websiteId - The website Id to be added to the file name.
     * @param null   $carrierCode
     */
    public function saveCSV($file, $fileName, $websiteId = null, $carrierCode = null)
    {
        $dir = Mage::getBaseDir('var') . '/export/';

        $prefix = 'WSA_' . $carrierCode . '_';//COMMON-38

        if (strpos($fileName, $prefix) !== 0) {
            $fileName = $prefix . $fileName;
        }

        $idStartPos = strpos($fileName, 'Id=');

        if ($idStartPos) {
            $fileName = substr_replace($fileName, '.csv', $idStartPos);
        }

        if (strpos($fileName, '.csv')) {
            $timestamp = md5(microtime());
            if (!is_null($websiteId)) {
                $fileName = str_replace('.csv', '', $fileName) . 'Id=' . $websiteId . '_' . $timestamp . '.csv';
            } else {
                $fileName = str_replace('.csv', '', $fileName) . $timestamp . '.csv';
            }
        }

        try {
            if (!is_dir($dir)) {
                if (!mkdir($dir)) {
                    Mage::helper('wsacommon/log')->postMajor("WSA Helper", "IO Error", "Error Creating Backup CSV File Directory");
                }
            }
            if (!ctype_digit(file_put_contents($dir . $fileName, $file))) {
                Mage::helper('wsacommon/log')->postMajor("WSA Helper", "IO Error", "Error Creating Backup CSV File");
            }
        } catch (Exception $e) {
            Mage::helper('wsacommon/log')->postMajor("Helper", "Error Saving CSV File Backup", $e->getMessage());
        }
    }

    public static function getProduct($item, $useParent = true)
    {
        $product = null;

        if ($item->getParentItem() != null && $useParent) {
            $product = $item->getParentItem()->getProduct();
        } else if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && !$useParent) {
            if ($item->getHasChildren()) {
                $children = count($item->getChildren()) ? $item->getChildren() : $item->getChildrenItems();//COMMON-37
                foreach ($children as $child) {
                    // like this for 1.6
                    $product = Mage::getModel('catalog/product')->load($child->getProductId());
                    break;
                }
            }
        } else {
            // like this for 1.6
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }

        return $product;
    }

    public function formatXML($xmlString)
    {

        try {
            $simpleXml = new SimpleXMLElement($xmlString);
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($simpleXml->asXML());

            return $dom->saveXML();
        } catch (Exception $e) {
            return $xmlString;
        }
    }
}