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
class MDN_AdvancedStock_Model_StockError extends Mage_Core_Model_Abstract {

    private $_hasError = false;

    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/StockError');
    }

    public function checkForError($stockId) {
        $stock = mage::getModel('cataloginventory/stock_item')->load($stockId);
        if ($stock->getId()) {
            //if product doesn't manage stock, return
            if (!$stock->ManageStock())
                return true;

            if (Mage::getStoreConfig('advancedstock/stock_errors/check_stock_values') == 1) {


                //compute expected quantities
                $expectedQty = (int) $stock->getQtyFromStockMovement();
                $t = mage::helper('AdvancedStock/Product_Ordered')->computeOrderedQty($stock->getproduct_id(), $stock);
                $expectedOrderedQty = $t['total'];
                $expectedReservedQty = mage::helper('AdvancedStock/Product_Reservation')->getReservedQtyForStock($stock, $stock->getproduct_id());

                //store data
                $this->setse_product_id($stock->getproduct_id());
                $this->setse_stock_id($stock->getId());
                $this->setse_stored_qty((int) $stock->getqty());
                $this->setse_expected_qty($expectedQty);
                $this->setse_stored_reserved_qty($stock->getstock_reserved_qty());
                $this->setse_expected_reserved_qty($expectedReservedQty);
                $this->setse_stored_ordered_qty($stock->getstock_ordered_qty());
                $this->setse_expected_ordered_qty($expectedOrderedQty);

                //check for errors
                $comments = '';
                if ($this->getse_stored_qty() != $this->getse_expected_qty()) {
                    $comments .= mage::helper('AdvancedStock')->__('Stored qty is incorrect (%s instead of %s)', $this->getse_stored_qty(), $this->getse_expected_qty()) . ', ';
                    $this->_hasError = true;
                }
                if ($this->getse_stored_reserved_qty() != $this->getse_expected_reserved_qty()) {
                    $comments .= mage::helper('AdvancedStock')->__('Stored reserved qty is incorrect (%s instead of %s)', $this->getse_stored_reserved_qty(), $this->getse_expected_reserved_qty()) . ', ';
                    $this->_hasError = true;
                }
                if ($this->getse_stored_ordered_qty() != $this->getse_expected_ordered_qty()) {
                    $comments .= mage::helper('AdvancedStock')->__('Stored ordered qty is incorrect (%s instead of %s)', $this->getse_stored_ordered_qty(), $this->getse_expected_ordered_qty()) . ', ';
                    $this->_hasError = true;
                }
                if ($this->getse_stored_qty() < 0) {
                    $comments .= mage::helper('AdvancedStock')->__('Quantity (%s) can not be negative', $this->getse_stored_qty()) . ', ';
                    $this->_hasError = true;
                }
                if ($this->getse_stored_reserved_qty() > $this->getse_stored_ordered_qty()) {
                    $comments .= mage::helper('AdvancedStock')->__('Reserved qty (%s) cant be greater than ordered qty (%s) ', $this->getse_stored_reserved_qty(), $this->getse_stored_ordered_qty()) . ', ';
                    $this->_hasError = true;
                }
                if ($this->getse_stored_reserved_qty() > $this->getse_stored_qty()) {
                    $comments .= mage::helper('AdvancedStock')->__('Reserved qty (%s) cant be greater than qty (%s)', $this->getse_stored_reserved_qty(), $this->getse_stored_qty()) . ', ';
                    $this->_hasError = true;
                }
            }

            //dispatch event to allow other modules to add their own stock error logic
            try {
                Mage::dispatchEvent('advancedstock_check_stock_error', array('stock' => $stock, 'comments' => $comments, 'has_error' => $this->_hasError));
            } catch (Exception $ex) {
                $comments .= $ex->getMessage() . ', ';
                $this->_hasError = true;
            }

            $this->setse_comments($comments);
        }

        return true;
    }

    /**
     * Return true if stock has error
     *
     * @return unknown
     */
    public function hasError() {
        return ($this->_hasError);
    }

    /**
     * Fix error (at least try...)
     *
     */
    public function fix() {

        $startTimestamp = time();
        $debug = '<p>Start for product #' . $this->getse_product_id();

        //re-calculate all information
        $product = mage::getModel('catalog/product')->load($this->getse_product_id());
        $debug .= '<p>Product loaded (' . (time() - $startTimestamp) . ' s)';
        mage::helper('AdvancedStock/Product_Base')->updateStocks($product);
        $debug .= '<p>Stocks updated (' . (time() - $startTimestamp) . ' s)';

        //if reserved qty higher than stock, fix it
        if ($this->getse_expected_qty() < $this->getse_expected_reserved_qty())
        {
            Mage::helper('AdvancedStock/Product_Reservation')->FixOverReservation($this->getse_stock_id());
        }
        
        //dispatch event to ask other extensions to fix issue
        Mage::dispatchEvent('advancedstock_fix_stock_error', array('product' => $product));
        $debug .= '<p>Event dispatched (' . (time() - $startTimestamp) . ' s)';

        //check if pb is fixed
        $this->checkForError($this->getse_stock_id());
        $debug .= '<p>Second check errors (' . (time() - $startTimestamp) . ' s)';

        if (!$this->hasError())
            $this->delete();
        else {
            $this->save();
            throw new Exception(mage::helper('AdvancedStock')->__('System is unable to fix error : error must be fixed manually'));
        }
    }

}