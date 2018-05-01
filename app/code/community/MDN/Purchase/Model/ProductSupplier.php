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
class MDN_Purchase_Model_ProductSupplier extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('Purchase/ProductSupplier');
    }

    /**
     * Retrieve price for produt
     *
     */
    public function getProductForSupplier($productId, $supplierId) {
        $item = $this->getCollection()
                        ->addFieldToFilter('pps_product_id', $productId)
                        ->addFieldToFilter('pps_supplier_num', $supplierId)
                        ->getFirstItem();
        if ($item->getId())
            return $item;
        else
            return 0;
    }

    /**
     * return suppliers (and information) for product
     *
     * @param unknown_type $product
     */
    public function getSuppliersForProduct($product) {
        $collection = mage::getModel('Purchase/ProductSupplier')
                        ->getCollection()
                        ->join('Purchase/Supplier', 'pps_supplier_num=sup_id')
                        ->addFieldToFilter('pps_product_id', $product->getId())
                        ->setOrder('pps_last_unit_price', 'ASC');
        return $collection;
    }

    /**
     * when saving, update supply needs for product
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        $productId = $this->getpps_product_id();

        //if pps_is_default_supplier change from 0 to 1, ensure that there is no other supplier having pps_is_default_supplier = 1
        if (($this->getOrigData('pps_is_default_supplier') != 1) && ($this->getpps_is_default_supplier() == 1)) {
            $productSuppliers = mage::getModel('Purchase/ProductSupplier')
                            ->getCollection()
                            ->addFieldToFilter('pps_product_id', $productId)
                            ->addFieldToFilter('pps_num', array('neq' => $this->getId()));
            foreach ($productSuppliers as $productSupplier) {
                if ($productSupplier->getpps_is_default_supplier() == 1)
                    $productSupplier->setpps_is_default_supplier(0)->save();
            }
        }

       
        //if pps_last_price change, update product cost
        if ($this->getpps_last_price() != $this->getOrigData('pps_last_price')) {
            mage::helper('BackgroundTask')->AddTask('Update cost for product #' . $productId. ' ( pps last price changed)',
                    'purchase/Product',
                    'updateProductCost',
                    $productId,
                    null,
                    true,
                    5
            );
        }

        //if pps_supply_delay change, update product availability status
        if ($this->getpps_supply_delay() != $this->getOrigData('pps_supply_delay')) {
            mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId . ' (pps supply delay changed)',
                'SalesOrderPlanning/ProductAvailabilityStatus',
                'RefreshForOneProduct',
                $productId,
                null,
                true
            );
        }

        Mage::dispatchEvent('purchase_product_supplier_after_save', array('product_supplier' => $this));
    }

    /**
     * when deleting, update supply needs for product
     *
     */
    protected function _afterDelete() {
        parent::_afterDelete();
        $productId = $this->getpps_product_id();
        mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId . ' (pps deleted)',
            'SalesOrderPlanning/ProductAvailabilityStatus',
            'RefreshForOneProduct',
            $productId,
            null,
            true
        );

        Mage::dispatchEvent('purchase_product_supplier_after_delete', array('product_id' => $productId));
    }

    /**
     * Return magento productid for supplier sku
     * @param <type> $supplierSku 
     */
    public function getProductIdFromSupplierSku($supplierSku, $supplierId) {
        return $this->_getResource()->getProductIdForSupplierSku($supplierSku, $supplierId);
    }

    public function getSupplierSku($productId, $supplierId) {
        return $this->_getResource()->getSupplierSku($productId, $supplierId);
    }

    public function loadForProductSupplier($productId, $supplierId) {
        $item = $this->getCollection()
                        ->addFieldToFilter('pps_product_id', $productId)
                        ->addFieldToFilter('pps_supplier_num', $supplierId)
                        ->getFirstItem();
        return $item;
    }

}