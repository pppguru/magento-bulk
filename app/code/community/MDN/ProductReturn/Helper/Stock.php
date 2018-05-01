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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_Stock extends Mage_Core_Helper_Abstract
{

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int $websiteId
     * @param string $description
     * @param MDN_ProductReturn_Model_Rma $rma
     */
    public function manageProductDestination($product, $websiteId, $description, $rma)
    {
        $productId   = Mage::getModel('catalog/product')->getResource()->getIdBySku($product['sku']);
        $qty         = $product['qty'];
        $destination = $product['destination'];
        $rpId = $product['rp_id'];

        $debug = 'Manage inventory for product #'.$productId.' with quantity '.$qty.' for destination : '.$destination.' ('.$description.')';
        Mage::helper('ProductReturn')->log($debug);

        $this->productBackInStockV2($productId, $qty, $destination, $websiteId, $description, $rma->getId());

        $rmaProduct = Mage::getModel('ProductReturn/RmaProducts')->load($rpId);
        $rmaProduct->setrp_destination_processed(1)->save();

        $product = Mage::getModel('catalog/product')->load($productId);
        $historyMsg = $qty.'x '.$product->getName().' '.$this->__($destination);
        $rma->addHistoryRma($historyMsg);
    }

    /**
     * @param int $productId
     * @param int $qty
     * @param string $destination
     * @param int $websiteId
     * @param string $description
     * @param null|int $rmaId
     */
    public function productBackInStockV2($productId, $qty, $destination, $websiteId, $description, $rmaId = null){

        if(Mage::Helper('ProductReturn')->erpIsInstalled() && preg_match('#^warehouse_#', $destination)){

            $product = mage::getModel('catalog/product')->load($productId);
            $warehouseId = str_replace('warehouse_', '',$destination);
            $this->_updateWarehouse($warehouseId, $product->getId(), $qty, $description);

        }elseif($destination == MDN_ProductReturn_Model_RmaProducts::kDestinationStock){

            $product = mage::getModel('catalog/product')->load($productId);
            $this->_updateStockItem($product, $qty);

        }

    }

    /**
     * @param int $warehouseId
     * @param int $productId
     * @param int $qty
     * @param string $description
     */
    protected function _updateWarehouse($warehouseId, $productId, $qty, $description){

        if (!empty($warehouseId) && !empty($productId)) {
            mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                $productId,
                null,
                $warehouseId,
                $qty,
                mage::helper('ProductReturn')->__($description),
                array('sm_type' => 'rma')
            );

        }

    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     */
    protected function _updateStockItem($product, $qty){

        if ($product->getId()) {
            $stockItem = $product->getStockItem();
            if ($stockItem) {
                $stockItem->setqty($stockItem->getqty() + $qty);
                $stockItem->save();
            }
        }

    }

}