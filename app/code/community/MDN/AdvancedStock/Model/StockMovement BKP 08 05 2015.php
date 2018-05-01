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
class MDN_AdvancedStock_Model_StockMovement extends Mage_Core_Model_Abstract {

    private $_targetWarehouse = null;
    private $_product = null;

    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/StockMovement');
    }

    /**
     * Update stocks qty (inbound and outbound stocks)
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        $productId = $this->getsm_product_id();
        $sourceStockId = $this->getsm_source_stock();
        $targetStockId = $this->getsm_target_stock();

        //update source stock
        if ($sourceStockId) {
            $stock = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $sourceStockId);
            if (!$stock)
                $stock = mage::getModel('cataloginventory/stock_item')->createStock($productId, $sourceStockId);
            $stock->storeQty();
        }

        //update target stock
        if ($targetStockId) {
            $stock = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $targetStockId);
            if (!$stock)
                $stock = mage::getModel('cataloginventory/stock_item')->createStock($productId, $targetStockId);
            $stock->storeQty();
        }
    }

    /**
     * Update product stock after delete
     *
     * @return unknown
     */
    protected function _afterDelete() {
        parent::_afterDelete();

        $productId = $this->getsm_product_id();

        mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($productId, 'stock mvt deleted');

    }

    /**
     * Function to check if a stock movement is possible
     *
     */
    public function validateStockMovement($productId, $sourceWarehouseId, $targetWarehouseId, $qty) {
        if (($sourceWarehouseId == '') && ($targetWarehouseId == ''))
            throw new Exception(mage::helper('AdvancedStock')->__('Please select warehouse'));

        if ($sourceWarehouseId == $targetWarehouseId)
            throw new Exception(mage::helper('AdvancedStock')->__('Warehouses are identical'));

        if (($qty <= 0) || (!is_numeric($qty)))
            throw new Exception(mage::helper('AdvancedStock')->__('Quantity incorrect for stock movement (qty = %s, productId = %s)', $qty, $productId));

        if ($sourceWarehouseId != '') {
            $sourceStock = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $sourceWarehouseId);
            if ($sourceStock) {
                if ($qty > ($sourceStock->getqty() - $sourceStock->getstock_reserved_qty()))
                    throw new Exception(mage::helper('AdvancedStock')->__('Requested quantity is not available'));
            }
            else {
                throw new Exception(mage::helper('AdvancedStock')->__('There is no source stock for this product'));
            }
        }

        return true;
    }

    /**
     * Create new stock movement
     *
     * @param unknown_type $productId
     * @param unknown_type $sourceWarehouseId
     * @param unknown_type $targetWarehouseId
     * @param unknown_type $qty
     * @param unknown_type $description
     */
    public function createStockMovement($productId, $sourceWarehouseId, $targetWarehouseId, $qty, $description, $additionalDatas = null) {
        try {
            //check if stock for product / target warehouse exist
            $model = mage::getModel('cataloginventory/stock_item');
            $stock = $model->loadByProductWarehouse($productId, $targetWarehouseId);
            if ($stock == null) {
                //create stock
                if ($targetWarehouseId)
                    $model->createStock($productId, $targetWarehouseId);
            }

            $obj = mage::getModel('AdvancedStock/StockMovement')
                            ->setsm_product_id($productId)
                            ->setsm_qty($qty)
                            ->setsm_description($description)
                            ->setsm_date(date('Y-m-d H:i'))
                            ->setsm_source_stock($sourceWarehouseId)
                            ->setsm_target_stock($targetWarehouseId);

            //save additional datas
            if (is_array($additionalDatas)) {
                foreach ($additionalDatas as $key => $value)
                    $obj->setData($key, $value);
            }

            $obj->save();

            return $obj;
        } catch (Exception $ex) {
            Mage::logException($ex);
            throw new Exception(mage::helper('AdvancedStock')->__('Unable to create stock movement : ') . $ex->getMessage());
        }
    }

    /**
     * Return target warehouse object
     *
     */
    public function getTargetWarehouseName() {
        if ($this->_targetWarehouse == null) {
            $this->_targetWarehouse = mage::getModel('AdvancedStock/Warehouse')->load($this->getsm_target_stock());
        }
        return $this->_targetWarehouse;
    }

    /**
     * return product object
     *
     */
    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getsm_product_id());
        }
        return $this->_product;
    }

    /**
     * Retourne les types possibles pour un mouvement de stock
     *
     */
    public function GetTypes() {
        $retour = array();
        $retour['supply'] = mage::helper('AdvancedStock')->__('supply');
        $retour['order'] = mage::helper('AdvancedStock')->__('order');
        $retour['rma'] = mage::helper('AdvancedStock')->__('rma');
        $retour['donation'] = mage::helper('AdvancedStock')->__('donation');
        $retour['lost'] = mage::helper('AdvancedStock')->__('lost/broken');
        $retour['loan'] = mage::helper('AdvancedStock')->__('loan');
        $retour['return'] = mage::helper('AdvancedStock')->__('Customer Return');
        $retour['adjustment'] = mage::helper('AdvancedStock')->__('Adjustment');
        $retour['creditmemo'] = mage::helper('AdvancedStock')->__('Creditmemo');
        $retour['rma_reservation'] = mage::helper('AdvancedStock')->__('Rma reservation');
        $retour['transfer'] = mage::helper('AdvancedStock')->__('Transfer');

        return $retour;
    }

}