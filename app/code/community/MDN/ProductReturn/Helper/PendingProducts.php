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
class MDN_ProductReturn_Helper_PendingProducts extends Mage_Core_Helper_Abstract
{
    /**
     * return collection for pending products
     *
     */
    public function getList()
    {
        $managedDestinations   = array();
        $managedDestinations[] = MDN_ProductReturn_Model_RmaProducts::kDestinationDestroy;
        $managedDestinations[] = MDN_ProductReturn_Model_RmaProducts::kDestinationStock;
        $managedDestinations[] = MDN_ProductReturn_Model_RmaProducts::kDestinationSupplier;

        $collection = mage::getModel('ProductReturn/RmaProducts')
            ->getCollection()
            ->join('ProductReturn/Rma', 'rma_id=rp_rma_id')
            ->addFieldToFilter('rma_reception_date', array('gt' => date('Y-m-d', 0))) //means rma_reception_date not null
            ->addFieldToFilter('rp_qty', array('gt' => 0))
            ->addFieldToFilter('rp_destination', array('in' => $managedDestinations))
            ->addFieldToFilter('rp_physically_processed', 0);


        return $collection;
    }

    /**
     * Print pending products
     *
     * @param unknown_type $productIds
     *
     * @return unknown
     */
    public function PrintPendingProducts($productIds)
    {
        $obj = mage::getModel('ProductReturn/Pdf_PendingProducts');
        $pdf = $obj->getPdf($productIds);

        return $pdf;
    }

    /**
     * Process products
     *
     * @param unknown_type $productIds
     */
    public function processProducts($productIds)
    {
        $collection = mage::getModel('ProductReturn/RmaProducts')
            ->getCollection()
            ->addFieldToFilter('rp_destination', array('neq' => MDN_ProductReturn_Model_RmaProducts::kDestinationSupplier))
            ->addFieldToFilter('rp_id', array('in' => $productIds));
        foreach ($collection as $item) {
            $item->setrp_physically_processed(1)->save();
        }
    }
}
