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
class MDN_AdvancedStock_Helper_ImportShelfLocation extends Mage_Core_Helper_Abstract {

    const allowedLineCountForImport = 2;
    const skuColumnId = 0;
    const shelfLocationColumnId = 1;
    private $_connection = null;
    /**
     *
     * @param type $lines
     * @param type $warehouseId
     * @param type $date
     * @param type $smCaption
     * @return string 
     */
    public function process($lines, $warehouseId) {
        $result = '';

        //parse lines
        $lineCount = 0;
        $lineProcessed = 0;


        foreach ($lines as $line) {

            $lineCount++;


            if (!$warehouseId) {
                $result .= '<font color="red">' . mage::helper('AdvancedStock')->__('Warehouse is not defined : ' . $warehouseId) . '</font><br>';
                continue;
            }

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
            $shelfLocation = trim($fields[self::shelfLocationColumnId]);

            if(strlen($sku) == 0){
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' :  SKU is empty is <b>'.$sku.'</b> : SKIPPED').'</font><br>';
                continue;
            }

            if(strlen($shelfLocation) == 0){
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' :  Shelf Location is empty <b>'.$shelfLocation.'</b> : SKIPPED').'</font><br>';
                continue;
            }


            //process
            $productId = mage::getModel('catalog/product')->getIdBySku($sku);

            if (!$productId){
                $result .= '<font color="red">'.mage::helper('AdvancedStock')->__('Line ' . $lineCount . ' : Sku : <b>' . $sku . '</b> does not exist.').'</font><br>';
            } else {
                $this->updateShelfLocation($productId, $warehouseId, $shelfLocation);
                $lineProcessed++;
            }
        }

        $result = '<p><b>'.mage::helper('AdvancedStock')->__($lineProcessed . ' lines successfully processed on '.$lineCount.' lines').'</b></p>' . $result;

        return $result;
    }


    private function updateShelfLocation($productId, $warehouseId, $shelfLocation) {
        $sql = 'UPDATE '.Mage::getConfig()->getTablePrefix().'cataloginventory_stock_item ';
        $sql .= 'SET shelf_location = "'.$shelfLocation.'" ';
        $sql .= 'WHERE product_id = '.$productId.' AND stock_id = '.$warehouseId;
        $this->getConnection()->query($sql);
    }

    private function getConnection(){
        if(!$this->_connection){
            $this->_connection = mage::getResourceModel('sales/order_item_collection')->getConnection();
        }
        return $this->_connection;
    }

}