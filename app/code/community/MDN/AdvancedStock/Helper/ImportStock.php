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
class MDN_AdvancedStock_Helper_ImportStock extends Mage_Core_Helper_Abstract {

    const allowedLineCountForImport = 2;
    const skuColumnId = 0;
    const QtyColumnId = 1;
    
    /**
     *
     * @param type $lines
     * @param type $warehouseId
     * @param type $date
     * @param type $smCaption
     * @return string 
     */
    public function process($lines, $warehouseId, $date, $smCaption) {
        $result = '';

        //parse lines
        $lineCount = 0;
        $lineProcessed = 0;

        //if today, push minute and second in case of multiple import in the same day to get stock movement in the correct order
        if((date('Ymd') == date('Ymd', strtotime($date)) || (!$date))){
            $date = date('Y-m-d H:i:s');
        }

        $additionalDatas = array('sm_date' => $date, 'sm_type' => 'adjustment');

        if(!$smCaption){
            $smCaption = mage::helper('AdvancedStock')->__('Manual import from csv');
        }else{
            $smCaption = trim($smCaption);
        }

        foreach ($lines as $line) {

            $lineCount++;

            //skip any empty line
            $line = trim($line);
            if(strlen($line) == 0){
               $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line %s is empty : skipped ',$lineCount).'</font><br>';
               continue;
            }

            //clean useless separators
            $line = str_replace('"', "", $line);
            $line = str_replace("'", "", $line);


            //explode fields
            $fields = explode(';', trim($line));
            $columnCount =  count($fields);
            if($columnCount != self::allowedLineCountForImport){           
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line "' . $lineCount . '" : is not correct, column count is '.$columnCount.', allowed is '.self::allowedLineCountForImport).'</font><br>';
                continue;
            }

            //get data
            $sku = trim($fields[self::skuColumnId]);
            $qty = trim($fields[self::QtyColumnId]);

            if(strlen($sku) == 0){
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' :  SKU is empty is <b>'.$sku.'</b> : SKIPPED').'</font><br>';
                continue;
            }

            if(strlen($qty) == 0){
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' :  QTY is empty is <b>'.$qty.'</b> : SKIPPED').'</font><br>';
                continue;
            }

            if(!is_numeric($qty)){
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' :  is not correct, QTY is <b>'.$qty.'</b>, dont seems to be a correct number').'</font><br>';
                continue;               
            }

            //process
            $productId = mage::getModel('catalog/product')->getIdBySku($sku);

            if (!$productId){
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' : Sku : <b>' . $sku . '</b> does not exist.').'</font><br>';
            } else {
                $stockLevelAtDate = 0;
                $stockItem = $this->loadByProductWarehouse($productId, $warehouseId);
                if ($stockItem) {

                    $stockLevelAtDate = $stockItem->getQtyFromStockMovement($date);
                    if (!$stockLevelAtDate)
                        $stockLevelAtDate = 0;
                }

                //if stocks are different, create stock movement
                if ($stockLevelAtDate != $qty) {

                    $result .= '<font color="black">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' : Stock level for Sku=' . $sku . ' changed from ' . $stockLevelAtDate . ' to ' . $qty ).'</font><br>';

                    $diff = $qty - $stockLevelAtDate;
                    if ($diff > 0) {
                        $sourceWarehouseId = null;
                        $targetWarehouseId = $warehouseId;
                    } else {
                        $sourceWarehouseId = $warehouseId;
                        $targetWarehouseId = null;
                        $diff = - $diff;
                    }

                    //create stock movement                    
                    mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                            $productId,
                            $sourceWarehouseId,
                            $targetWarehouseId,
                            $diff,
                            $smCaption,
                            $additionalDatas
                    );                    
                    
                }
                $lineProcessed++;
            }
        }

        $result = '<p><b>'.mage::helper('AdvancedStock')->__($lineProcessed . ' lines successfully processed on '.$lineCount.' lines').'</b></p>' . $result;

        return $result;
    }

    /**
     *
     * @param type $productId
     * @param type $warehouseId
     * @return null 
     */
    private function loadByProductWarehouse($productId, $warehouseId) {
        $item = mage::getModel('cataloginventory/stock_item')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('stock_id', $warehouseId)
                ->getFirstItem();
        if ($item->getId())
            return $item;
        else
            return null;
    }

}