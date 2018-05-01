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
class MDN_Purchase_Helper_PreferedStockLevel extends Mage_Core_Helper_Abstract {

    /**
     * Import prefered stock levels
     */
    public function import($lines) {
        $skipped = 0;
        $imported = 0;
        $wrongSku = 0;
        $unknownSkus = '';

        foreach ($lines as $line) {
            //retrieve information
            $t = explode(';', $line);
            if (count($t) != 3) {
                $skipped++;
                continue;
            }
            $sku = $t[0];
            $warningStockLevel = $t[1];
            $idealStockLevel = $t[2];

            //find product
            $productId = mage::getModel('catalog/product')->getIdBySku($sku);
            if (!$productId) {
                $wrongSku++;
                $unknownSkus .= $sku . ',';
                continue;
            }

            //update
            //todo : upload both warning & ideal stocks
            $debug = '<p>Sku=' . $sku;
            $stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
            foreach ($stocks as $stock) {
                $debug .= '<br>stock id = ' . $stock->getId();
                if ($stock->getstock_disable_supply_needs() != 1) {
                    $debug .= '<br>Supply needs enabled';
                    $updated = false;

                    if ($stock->getWarningStockLevel() != $warningStockLevel) {
                        $debug .= '<br>Change warning stock level to ' . $warningStockLevel;
                        $stock->setnotify_stock_qty($warningStockLevel)
                                ->setuse_config_notify_stock_qty(0);
                        $updated = true;
                    }

                    if ($stock->getIdealStockLevel() != $idealStockLevel) {
                        $debug .= '<br>Change ideal stock level to ' . $idealStockLevel;
                        $stock->setideal_stock_level($idealStockLevel)
                                ->setuse_config_ideal_stock_level(0);
                        $updated = true;
                    }
                    
                    //
                    if ($updated)
                        $stock->save();
                }
                else
                    $debug .= '<br>Supply needs disabled';
            }
            $imported++;
        }

        //return results
        $result = $this->__('Import complete : %s imported, %s skipped, %s unknown skus (%s)', $imported, $skipped, $wrongSku, $unknownSkus);
        return $result;
    }

}