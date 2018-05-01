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
class MDN_AdvancedStock_Model_ProductSerial extends Mage_Core_Model_Abstract {

    /**
     * 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/ProductSerial');
    }

    /**
     * 
     * @param type $orderItem
     */
    public function updateForOrderItem($orderItem)
    {
        //todo : check old serials associated
        
        //process current values
        $serials = explode("\n", $orderItem->getErpOrderItem()->getSerials());
        foreach($serials as $serial)
        {
            if (!$serial)
                continue;
            
            //load (if inserted from PO) or create item
            $productSerial = Mage::getModel('AdvancedStock/ProductSerial')->load($serial, 'pps_serial');
            if (!$productSerial->getId())
            {
                $productSerial->setpps_product_id($orderItem->getproduct_id());
                $productSerial->setpps_serial($serial);
            }
            
            //update with information
            $productSerial->setpps_salesorder_id($orderItem->getorder_id());
            $productSerial->save();
        }
        
        return true;
    }
    
    /**
     * 
     * @param type $poItem
     * @param type $serialNumbers
     */
    public function updateForPurchaseOrderItem($poItem, $serialNumbers)
    {
        foreach($serialNumbers as $sn)
        {
            if (!$sn)
                continue;
            
            $productSerial = Mage::getModel('AdvancedStock/ProductSerial');
            $productSerial->setpps_product_id($poItem->getpop_product_id());
            $productSerial->setpps_purchaseorder_id($poItem->getpop_order_num());
            $productSerial->setpps_serial($sn);
            $productSerial->save();
        }
    }

}