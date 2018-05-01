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
class MDN_AdvancedStock_Helper_ProductReturn_Stock extends MDN_ProductReturn_Helper_Stock {

    /**
     * Overload product return helper to increase stock using stock movement when a product back to stock
     *
     * @param unknown_type $productId
     * @param unknown_type $qty
     */
    public function productBackInStock($productId, $qty, $destination, $websiteId, $description, $rmaId = null)  {
        switch ($destination) {
            case MDN_ProductReturn_Model_RmaProducts::kDestinationCustomer:
                //nothing
                break;
            case MDN_ProductReturn_Model_RmaProducts::kDestinationDestroy:
                //nothing
                break;
            case MDN_ProductReturn_Model_RmaProducts::kDestinationStock:
                //create new stock movement
                $targetWarehouse = mage::helper('AdvancedStock/Warehouse')->getWarehouseForAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentProductReturn);
                if ($targetWarehouse) {
                    $additionalData = array('sm_type' => 'rma');
                    mage::getModel('AdvancedStock/StockMovement')->createStockMovement($productId,
                            null,
                            $targetWarehouse->getId(),
                            $qty,
                            $description,
                            $additionalData);
                }
                else
                       throw new Exception('No product return warehouse assignments for website #'.$websiteId);
                break;
            case MDN_ProductReturn_Model_RmaProducts::kDestinationSupplier:
                //nothing
                break;
        }
    }

}
