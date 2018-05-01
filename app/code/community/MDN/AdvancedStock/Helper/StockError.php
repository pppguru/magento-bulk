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
class MDN_AdvancedStock_Helper_StockError extends Mage_Core_Helper_Abstract {

    /**
     * Update stock error for 1 product
     *
     * @param unknown_type $productId
     */
    public function updateStockError($stockId) {
        //save record if has error
        $obj = mage::getModel('AdvancedStock/StockError');
        $obj->checkForError($stockId);
        if ($obj->hasError()) {
            $obj->save();
        }
    }

    /**
     * Truncate table
     *
     */
    public function truncateTable() {
        mage::getResourceModel('AdvancedStock/StockError')->TruncateTable();
    }

    /**
     * refresh results
     *
     */
    public function refresh() {
        //truncate table
        $this->truncateTable();

        //create group
        $taskGroup = 'refresh_stock_errors';
        mage::helper('BackgroundTask')->AddGroup($taskGroup,
                mage::helper('AdvancedStock')->__('Refresh stock errors'),
                'AdvancedStock/Misc/IdentifyErrors');

        //get stock_item ids
        $ids = mage::getModel('cataloginventory/stock_item')
                        ->getCollection()
                        ->getAllIds();

        //plan multiple task using lots of 1000 items
        $start = 0;
        $max = count($ids);
        $lot = 1000;
        while ($start < $max)
        {
            $arrayTmp = array_slice($ids, $start, $lot);
            mage::helper('BackgroundTask')->AddMultipleTask(
                    $arrayTmp,
                    'Update stock errors for stock #{id}',
                    'AdvancedStock/StockError',
                    'updateStockError',
                    $taskGroup
            );
            $start += $lot;
        }

        //execute task group
        mage::helper('BackgroundTask')->ExecuteTaskGroup($taskGroup);
    }

    /**
     * Fix all errors
     *
     */
    public function fixAllErrors() {
        $taskGroup = 'fix_stock_errors';
        mage::helper('BackgroundTask')->AddGroup($taskGroup,
                mage::helper('AdvancedStock')->__('Fix stock errors'),
                'AdvancedStock/Misc/IdentifyErrors');

        //plan task for error
        $collection = mage::getModel('AdvancedStock/StockError')->getCollection();
        foreach ($collection as $error) {
            $errorId = $error->getId();
            mage::helper('BackgroundTask')->AddTask('Fix stock error #' . $errorId,
                    'AdvancedStock/StockError',
                    'fixStockError',
                    $errorId,
                    $taskGroup
            );
        }

        //execute task group
        mage::helper('BackgroundTask')->ExecuteTaskGroup($taskGroup);
    }

    /**
     * Fix one error
     *
     * @param unknown_type $errorId
     */
    public function fixStockError($errorId) {
        try {
            $stockError = mage::getModel('AdvancedStock/StockError')->load($errorId);
            if ($stockError->getId())
                $stockError->fix();
        } catch (Exception $ex) {
            //nothing
        }
    }

}