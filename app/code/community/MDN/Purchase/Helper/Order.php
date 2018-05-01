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
class MDN_Purchase_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Create new order
     */
    public function createNewOrder($supplierId) {

        $supplier = Mage::getModel('Purchase/Supplier')->load($supplierId);

        //define currency
        $currency = Mage::getStoreConfig('purchase/purchase_order/default_currency');
        if ($supplier->getsup_currency())
                $currency = $supplier->getsup_currency();

        //define default shipping tax rate
        $taxRate = Mage::getStoreConfig('purchase/purchase_order/default_shipping_duties_taxrate');
        if($supplier->getTaxRate() !==  NULL
            && $supplier->getTaxRate()->getId() > 0
            && $supplier->getTaxRate()->getptr_value() >= 0) {
            $taxRate = $supplier->getTaxRate()->getptr_value();
        }

        //create order
        $model = mage::getModel('Purchase/Order');
        $order = $model
                        ->setpo_sup_num($supplierId)
                        ->setpo_date(date('Y-m-d'))
                        ->setpo_currency($currency)
                        ->setpo_tax_rate($taxRate)
                        ->setpo_order_id($model->GenerateOrderNumber())
                        ->setpo_status('new')
                        ->save();

        //add comment
        $order->addHistory($this->__('Purchase order created'));

        return $order;
    }

    /**
     * Add an history entry without loading the PO model
     * Usage :
     * Mage::helper('purchase/Order')->addHistoryMessage($poId, $msg);
     *
     * @param $poId int
     * @param $msg string
     * @param $timestamp $date
     */
    public function addHistoryMessage($poId, $msg, $timestamp = null)
    {
        if ($timestamp == null)
            $timestamp = mage::getModel('core/date')->timestamp();

        $history = Mage::getModel('Purchase/Order_History')
            ->setpoh_po_id($poId)
            ->setpoh_created_at($timestamp)
            ->setpoh_message($msg)
            ->save();

        return $history;
    }

}