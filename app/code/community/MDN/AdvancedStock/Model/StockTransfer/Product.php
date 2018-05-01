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
class MDN_AdvancedStock_Model_StockTransfer_Product extends Mage_Core_Model_Abstract
{


    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('AdvancedStock/StockTransfer_Product');
    }

    /**
     *
     * @return <type> 
     */
    public function getQtyToTransfer()
    {
        $qtytoTransfert = $this->getstp_qty_requested() - $this->getstp_qty_transfered();
        if($qtytoTransfert<0){
            $qtytoTransfert = 0;
        }
        return $qtytoTransfert;
    }

    /**
     * Apply transfer for this product
     * @param <type> $transfer
     */
    public function applyTransfer($transfer)
    {
        //get data
        $transferableQty = $this->getTransferableQty($transfer);
        $productId = $this->getstp_product_id();
        $sourceWarehouse = $transfer->getst_source_warehouse();
        $targetWarehouse = $transfer->getst_target_warehouse();
        $transferName = $transfer->getst_name();

        //create stock movement
        $additionalData = array('sm_type' => 'transfer');
        $stockMovement = mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
                $productId,
                $sourceWarehouse,
                $targetWarehouse,
                $transferableQty,
                $transferName,
                $additionalData);

        //update transfered qty
        $this->setstp_qty_transfered($this->getstp_qty_transfered() + $transferableQty)->save();

        return $stockMovement;
    }

    /**
     * Check if transfer is appliable
     * @param <type> $transfer
     */
    public function canBeApplied($transfer)
    {
        $transferableQty = $this->getTransferableQty($transfer);
        $qtyToTransfer = $this->getQtyToTransfer();
        return ($qtyToTransfer <= $transferableQty);
    }

    /**
     * Return transferable qty depending of source warehouse
     * @param <type> $transfer
     */
    public function getTransferableQty($transfer)
    {
        $qtyToTransfer = $this->getQtyToTransfer();
        $sourceWarehouse = $transfer->getst_source_warehouse();
        if ($sourceWarehouse)
        {
            $productId = $this->getstp_product_id();
            $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $sourceWarehouse);
            $availableQty = 0;
            if ($stockItem)
                $availableQty = $stockItem->getAvailableQty();

            if ($availableQty < $qtyToTransfer)
                return $availableQty;
            else
                return $qtyToTransfer;

        }
        else
            return $qtyToTransfer;
    }
}