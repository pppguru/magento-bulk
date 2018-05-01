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
class MDN_Orderpreparation_Helper_Invoice extends Mage_Core_Helper_Abstract {

    /**
     * Store invoice id in ordertoprepare model
     *
     * @param unknown_type $OrderId
     * @param unknown_type $InvoiceId
     */
    public function StoreInvoiceId($OrderId, $InvoiceId) {
		$item = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($OrderId);
        $item->setinvoice_id($InvoiceId)->save();
    }

    /*
     * Check if invoice is created for 1 order
     *
     */

    public function InvoiceCreatedForOrder($OrderId) {
		$item = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($OrderId);
        if (($item->getinvoice_id() == null) || ($item->getinvoice_id() == ''))
            return false;
        else
            return true;
    }

    /**
     * Create invoice for order
     *
     * @param unknown_type $order
     */
    public function CreateInvoice(&$order) {
        $debug = 'Create invoice for order #'.$order->getIncrementId();

        try {
            
            if (!$order->canInvoice()) {
                $debug .= ' : Can not invoice !';
                mage::log($debug, null, 'erp_create_invoice.log');
                return false;
            }

            //Get data
            $order_to_prepare = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($order->getId());

            Mage::dispatchEvent('orderpreparartion_before_create_invoice', array('order' => $order));

            //create an array with items to invoice
            $shippedItems = array();
            if (!Mage::getStoreConfig('orderpreparation/create_shipment_and_invoices_options/invoice_only_shipped_items'))
            {
                foreach ($order->getAllItems() as $item) {
                    $shippedItems[$item->getId()] = $item->getqty_ordered();
                    $debug .= ', ordeitemid = '.$item->getId().' qty = '.$item->getqty_ordered();
                }
            }
            else
            {
                //create an array with shipped items
                $collection = Mage::getModel('Orderpreparation/ordertoprepare')->GetItemsToShip($order->getId());
                foreach($collection as $item)
                {
                    $shippedItems[$item->getorder_item_id()] = $item->getqty();
                    $debug .= ', ordeitemid = '.$item->getId().' qty = '.$item->getqty();
                }
                
                //add other items with 0 qty
                foreach ($order->getAllItems() as $item) {
                    if (!isset($shippedItems[$item->getId()]))
                        $shippedItems[$item->getId()] = 0;
                }
            }

            //create invoice
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($shippedItems);
            if ($invoice->canCapture())
            {
                $captureMode = '';
                //if ($invoice->getOrder()->getPayment()->getMethodInstance()->isGateway())
                //    $captureMode = 'offline';
                //else
                //{
                    $captureMode = 'online';
                //}
                
                $debug .= ', capture invoice '.$captureMode;
                $invoice->setRequestedCaptureCase($captureMode);
            }
            else
                $debug .= ',do not capture invoice';
            
            //save invoice
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();
            //$invoice->save();

            //link order & invoice
            $this->StoreInvoiceId($order->getid(), $invoice->getincrement_id());
            $debug .= ', invoiceid = '.$invoice->getincrement_id();

            //validate payment
            //$payment = $order->getPayment();
            //$payment->pay($invoice);
            //$payment->save();


            //$order->save();

            Mage::dispatchEvent('orderpreparartion_after_create_invoice', array('order' => $order, 'invoice' => $invoice));

        } catch (Exception $ex) {
            $debug .= ', '.$ex->getMessage();
            mage::log($debug, null, 'erp_create_invoice.log');
            throw new Exception('Error while creating Invoice for Order ' . $order->getincrement_id() . ': ' . $ex->getMessage());
        }
        
        mage::log($debug, null, 'erp_create_invoice.log');
        return true;
    }

}
