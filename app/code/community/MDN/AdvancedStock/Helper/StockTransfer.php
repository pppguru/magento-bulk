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
class MDN_AdvancedStock_Helper_StockTransfer extends Mage_Core_Helper_Abstract {

    const allowedLineCountForImport = 2;
    const skuPositionForImport = 0;
    const qtyPositionForImport = 1;


    /**
     * Import products in file transfer
     *
     * @param type $lines
     * @param type $transferId
     * @param type $delimiterKeyCode
     * 
     * @return type
     * @throws Exception
     */
    public function importTransferProducts($lines, $transferId, $delimiterKeyCode){

        $productAddedCount = 0;
        $lineCount = 0;
        $log = '';        

        if($transferId>0){

            $delimiter = chr($delimiterKeyCode);
            
            $transfer = mage::getModel('AdvancedStock/StockTransfer')->load($transferId);

            if($transfer && $transfer->getId()>0){

                foreach ($lines as $line){

                    $lineCount++;

                    //skip any empty line
                    $line = trim($line);
                    if(strlen($line) == 0){
                       $log .= mage::helper('AdvancedStock')->__('Line %s is empty : skipped',$lineCount).'<br/>';
                       continue;
                    }

                    //Skip the csv header if it is really the correct header
                    if(($lineCount == 1) && (strpos(strtolower($line), 'sku') !== FALSE) && (strpos(strtolower($line), 'qty') !== FALSE)){
                        $log .= mage::helper('AdvancedStock')->__('Line %s : Header found : skipped',$lineCount).'<br/>';
                        continue;
                    };

                    //clean useless separators
                    $line = str_replace('"', "", $line);
                    $line = str_replace("'", "", $line);

                    //split datas
                    $rowArray = explode($delimiter,trim($line));

                    //Check line consistancy and skip if no import possible
                    $columnCount =  count($rowArray);
                    if($columnCount != self::allowedLineCountForImport){
                      $log .= mage::helper('AdvancedStock')->__('Line %s is invalid : column count = %s, expected is %s',$lineCount,$columnCount,self::allowedLineCountForImport);
                      if(strpos($line, $delimiter) === FALSE){
                          $log .= ' : '.mage::helper('AdvancedStock')->__('Delimiter %s is not present',$delimiter);
                      }
                      $log .= '<br/>';
                      continue;
                    }

                    $sku = $rowArray[self::skuPositionForImport];
                    $qty = $rowArray[self::qtyPositionForImport];

                    $productId = mage::getModel('catalog/product')->getIdBySku($sku);

                    if($productId > 0 && $qty > 0){

                        $transfer->addProduct($productId, $qty);
                        $productAddedCount++;

                    }else{
                        $log .= mage::helper('AdvancedStock')->__('Sku or Qty is invalid for SKU=%s Qty=%s for line=%s',$sku,$qty,$lineCount).'<br/>';
                    }
                }
            }

        }else{
            throw new Exception(mage::helper('AdvancedStock')->__('Transfert Id %s is incorrect',$transferId));
        }

        $log .= mage::helper('AdvancedStock')->__('%s product added / %s lines processed',$productAddedCount,$lineCount).'<br/>';

        return $log;
    }
    
}
