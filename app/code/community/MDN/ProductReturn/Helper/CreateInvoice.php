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
 * @author     : Florent Plantinet
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_CreateInvoice extends MDN_ProductReturn_Helper_CreateAbstract
{

    public function CreateInvoice(&$order)
    {
        $debug = 'Create invoice for order #' . $order->getIncrementId();
        try {

            if (
                (!Mage::getStoreConfig('productreturn/product_return/auto_create_invoice'))
                || (!$order->canInvoice())
                || (Mage::getStoreConfig('productreturn/product_return/auto_create_invoice_onlyzero') && $order->getGrandTotal() > 0)
                )
            {
                $debug .= ' : Can not invoice !';
                mage::log($debug, null, 'productreturn_create_invoice.log');
                return false;
            }

            Mage::dispatchEvent('productreturn_before_create_invoice', array('order' => $order));

            //create an array with items to invoice
            $shippedItems = array();
            foreach ($order->getAllItems() as $item) {
                $shippedItems[$item->getId()] = $item->getqty_ordered();
                $debug .= ', ordeitemid = ' . $item->getId() . ' qty = ' . $item->getqty_ordered();
            }

            //create invoice
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($shippedItems);

            //save invoice
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $debug .= ', invoiceid = ' . $invoice->getincrement_id();

            Mage::dispatchEvent('productreturn_after_create_invoice', array('order' => $order, 'invoice' => $invoice));
        } catch
        (Exception $ex) {
            $debug .= ', ' . $ex->getMessage();
            mage::log($debug, null, 'productreturn_create_invoice.log');
            throw new Exception('Error while creating Invoice for Order ' . $order->getincrement_id() . ': ' . $ex->getMessage());
        }
        mage::log($debug, null, 'productreturn_create_invoice.log');

        return true;
    }

}