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
class MDN_AdvancedStock_Model_StockTransfer extends Mage_Core_Model_Abstract {
    const STATUS_NEW = 'new';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELED = 'canceled';

    private $_sourceWarehouse = null;
    private $_targetWarehouse = null;

    /**
     * Constructor
     */
    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/StockTransfer');
    }

    /**
     * return statuses
     *
     */
    public function getStatuses() {
        $retour = array();
        $retour[MDN_AdvancedStock_Model_StockTransfer::STATUS_NEW] = mage::helper('AdvancedStock')->__('New');
        $retour[MDN_AdvancedStock_Model_StockTransfer::STATUS_PARTIAL] = mage::helper('AdvancedStock')->__('Partial');
        $retour[MDN_AdvancedStock_Model_StockTransfer::STATUS_COMPLETE] = mage::helper('AdvancedStock')->__('Complete');
        $retour[MDN_AdvancedStock_Model_StockTransfer::STATUS_CANCELED] = mage::helper('AdvancedStock')->__('Canceled');
        return $retour;
    }

    /**
     * Before save
     */
    protected function _beforeSave() {
        parent::_beforeSave();

        if (!$this->getst_created_at())
            $this->setst_created_at(date('Y-m-d H:i'));
    }

    /**
     * After save
     */
    protected function _afterSave() {
        parent::_afterSave();

        Mage::dispatchEvent('stock_transfer_after_save', array('transfer' => $this));
    }

    /**
     * Add product to transfer
     * @param <type> $productId
     * @param <type> $qty
     */
    public function addProduct($productId, $qty) {

        if (!$productId)
            return false;
        
        $product = mage::getModel('catalog/product')->load($productId);
        $item = $this->getItemFromProductId($productId);
        if (!$item)
        {
            $item = mage::getModel('AdvancedStock/StockTransfer_Product');
            $item->setstp_transfer_id($this->getId())
                    ->setstp_product_id($productId)
                    ->setstp_qty_requested($qty)
                    ->setstp_product_name($product->getName())
                    ->setstp_product_sku($product->getSku());
            $item->save();
        }
        else
        {
            $item->setstp_qty_requested($item->getstp_qty_requested() + $qty);
            $item->save();
        }
        
        return $item;
    }

    /**
     * Return products collection
     */
    public function getProducts() {
        $collection = mage::getModel('AdvancedStock/StockTransfer_Product')
                        ->getCollection()
                        ->addFieldToFilter('stp_transfer_id', $this->getId());

        return $collection;
    }
    
    /**
     * Check if a product is already in a transfer
     * @param type $productId 
     */
    public function getItemFromProductId($productId)
    {
        foreach($this->getProducts() as $product)
        {
            if ($product->getstp_product_id() == $productId)
                return $product;
        }
        return null;
    }

    /**
     * Apply transfer
     */
    public function apply() {
        $this->canBeApplied();

        foreach ($this->getProducts() as $item) {
            //get qty to transfer
            $quantityToTransfer = $item->getTransferableQty($this);
            if ($quantityToTransfer <= 0)
                continue;

            //apply transfer
            $item->applyTransfer($this);
        }

        $this->updateStatus();
    }

    /**
     * Check if transfer is doable (check stock levels from source warehouse)
     * @return <type>
     */
    public function canBeApplied() {
        foreach ($this->getProducts() as $item) {
            if (!$item->canBeApplied($this)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return products for which we cant process transfer
     */
    public function getNotApplicableProducts() {
        $sourceWarehouseId = $this->getst_source_warehouse();

        $collection = mage::getResourceModel('AdvancedStock/StockTransfer_Product_Collection')
                        ->addFieldToFilter('stp_transfer_id', $this->getId())
                        ->join('cataloginventory/stock_item', 'stp_product_id=product_id and stock_id = ' . $sourceWarehouseId . ' and ((stp_qty_requested - stp_qty_transfered) - (qty - stock_reserved_qty) > 0)',
                                array());

        return $collection;
    }

    /**
     * Update stock transfer status
     */
    public function updateStatus() {
        //update status only if current status is new or partial
        switch ($this->getst_status()) {
            case self::STATUS_CANCELED:
            case self::STATUS_COMPLETE:
                return;
                break;
            default:
                $remainingToTransfer = mage::getResourceModel('AdvancedStock/StockTransfer_Product')->getRemainingToTransferSum($this->getId());
                $transfered = mage::getResourceModel('AdvancedStock/StockTransfer_Product')->getTransferedSum($this->getId());

                if (($remainingToTransfer == 0) && ($transfered > 0))
                    $this->setst_status(self::STATUS_COMPLETE)->save();
                if (($remainingToTransfer > 0) && ($transfered > 0))
                    $this->setst_status(self::STATUS_PARTIAL)->save();
                break;
        }
    }

    /**
     * Return source warehouse object
     * @return <type>
     */
    public function getSourceWarehouse() {
        if ($this->_sourceWarehouse == null) {
            $this->_sourceWarehouse = Mage::getModel('AdvancedStock/Warehouse')->load($this->getst_source_warehouse());
        }
        return $this->_sourceWarehouse;
    }

    /**
     * Return target warehouse object
     * @return <type>
     */
    public function getTargetWarehouse() {
        if ($this->_targetWarehouse == null) {
            $this->_targetWarehouse = Mage::getModel('AdvancedStock/Warehouse')->load($this->getst_target_warehouse());
        }
        return $this->_targetWarehouse;
    }

    /**
     * Add products in transfer based on needed products
     * @return int
     */
    public function populateWithSupplyNeeds()
    {
        $count = 0;

        $supplyNeeds = Mage::getModel('Purchase/SupplyNeedsWarehouse')
                                ->getCollection()
                                ->addFieldToFilter('stock_id', $this->getTargetWarehouse()->getId())
                                ->addFieldToFilter('status', MDN_Purchase_Model_SupplyNeeds::_StatusValidOrders);
        foreach($supplyNeeds as $sn)
        {
            if ($this->getSourceWarehouse()->getAvailableQty($sn->getproduct_id()) >= $sn->getqty_min())
            {
                $this->addProduct($sn->getproduct_id(), $sn->getqty_min());
                $count++;
            }
        }

        return $count;
    }

}