<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *refund
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_CreateCreditmemo extends MDN_ProductReturn_Helper_CreateAbstract
{

    public function CreateCreditmemo($object)
    {
        
        if (!$this->hasProducts($object)) {
            Mage::throwException($this->__('Can not do credit memo without product'));
        }

        $groupedProducts    = $this->getGroupedProductsByInvoices($object);
        $createdCreditmemos = array();
        $isOnline           = false;

        if ($object['refund_online']) {
            $isOnline = true;
        }
        
        foreach ($groupedProducts as $invoiceId => $data) {
            $creditmemo = $this->createInvoiceCreditmemo($invoiceId, $data, $object, $isOnline);
            $this->afterCreateCreditmemo($creditmemo, $object);
            $createdCreditmemos[] = $creditmemo;
        }

        return $createdCreditmemos;
    }

    /**
     * Main function, create credit memo using data
     *
     * @param      $invoiceId
     * @param      $rmaData
     * @param bool $isOnline
     *
     * @return
     * @internal param \unknown_type $object
     */
    protected function createInvoiceCreditmemo($invoiceId, $rmaData, $object, $isOnline = false)
    {
        $invoice = mage::getModel('sales/order_invoice')->load($invoiceId);

        if (!$invoice->getId()) {
            Mage::throwException($this->__('Unable to load sales invoice (%s)', $invoiceId));
        }

        $order = $invoice->getOrder();

        if (!$order->canCreditmemo()) {
            Mage::throwException($this->__('Can not do credit memo for order'));
        }

        //init credit memo
        $convertor        = Mage::getModel('sales/convert_order');
        $creditmemo       = $convertor->toCreditmemo($order)->setInvoice($invoice);
        $adjustmentRefund = 0;

        // add items
        if (isset($rmaData['items'])) {
            foreach ($rmaData['items'] as $itemData) {
                $orderItem = $itemData['item'];
                $orderItem  = $order->getItemById($orderItem->getitem_id());
                $qty       = $itemData['qty'];

                if ($qty == 0) {
                    if ($orderItem->isDummy()) {
                        if ($orderItem->getParentItem() && ($qty > 0)) {
                            /* 
                             * this part of code will never run, bacaouse of conditions: $qty==0 && $qty > 0 !!! 
                             */
                            $parentItemNewQty  = $this->getQtyForOrderItemId($object, $orderItem->getParentItem()->getId());
                            $parentItemOrigQty = $orderItem->getParentItem()->getQtyOrdered();
                            $itemOrigQty       = $orderItem->getQtyOrdered() / $parentItemOrigQty;
                            $qty               = $itemOrigQty * $parentItemNewQty;
                        }
                    }
                }
                $item = $convertor->itemToCreditmemoItem($orderItem);
                $item->setQty($qty);

                //customize price if partial refund
                $price = $this->getPriceForOrderItemId($object, $orderItem->getId());
                if ($price > 0) {
                    $adjustmentRefund += $item->getPrice() - $price;
                }
                

                $creditmemo->addItem($item);
            }
        }
        
        $rmaData['refund'] += $adjustmentRefund;

        //refund shipping fees
        if (isset($rmaData['refund_shipping_amount'])) {
            if ($order->getshipping_tax_amount() > 0)
                $shippingTaxCoef = $order->getshipping_amount() / $order->getshipping_tax_amount();
            else
                $shippingTaxCoef = 0;
            $refundAmountInclTax = $rmaData['refund_shipping_amount'];
            if ($shippingTaxCoef > 0 )
                $refundAmountTax = $refundAmountInclTax / $shippingTaxCoef;
            else
                $refundAmountTax = 0;
            $refundAmountExclTax = $refundAmountInclTax - $refundAmountTax;

            $creditmemo->setShippingAmount($refundAmountInclTax);
            $creditmemo->setBaseShippingAmount($refundAmountInclTax);
        } else {
            $creditmemo->setBaseShippingAmount(0.00);
        }


        //manage adjustement
        $creditmemo->setAdjustmentPositive($rmaData['fee']);
        $creditmemo->setAdjustmentNegative($rmaData['refund']);
        
        
        $creditmemo->setRefundRequested(true);
        $creditmemo->setOfflineRequested(!$isOnline);

        $creditmemo->collectTotals();
        $creditmemo->register();
        
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder())
            ->addObject($creditmemo->getInvoice());
        $transactionSave->save();

        
        return $creditmemo;
    }

    public function afterCreateCreditmemo($creditmemo, $object)
    {
        $rma = mage::getModel('ProductReturn/Rma')->load($object['rma_id']);

        //notify customer
        $creditmemo->sendEmail(true, '');

        //store creditmemo creation in history
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_creditmemo/view', array('creditmemo_id' => $creditmemo->getId(), 'key' => '[key]'));
        $rma->addHistoryRma('<a href="' . $url . '">' . mage::helper('ProductReturn')->__('Credit memo #%s created', $creditmemo->getincrement_id()) . '</a>');

        //store credit memo in rma/products
        foreach ($object['products'] as $product) {
            if ((isset($product['rp_id'])) && ($product['rp_id'] != null)) {
                //update information

                /**
                 * datas setrp_object_id && setrp_associated_object can be wrong storaged,
                 * because 1 rma product can be splitted into more then 1 creditmemos
                 */

                $rmaProduct = mage::getModel('ProductReturn/RmaProducts')->load($product['rp_id']);
                $rmaProduct->setrp_action_processed(1)
                    ->setrp_associated_object(mage::helper('ProductReturn')->__('Credit memo #%s', $creditmemo->getincrement_id()))
                    ->setrp_object_type('creditmemo')
                    ->setrp_object_id($creditmemo->getId())
                    ->setrp_action('refund')
                    ->setrp_destination($product['destination'])
                    ->save();
            }

        }

        $comment = mage::helper('ProductReturn')->__('Created for Product return #%s', $rma->getrma_ref());
        $creditmemo->addComment($comment, false);
        $creditmemo->save();
    }

    /**
     * Return products gropued by invoices
     *
     * @param unknown_type $object
     *
     * @return array
     */
    protected function getGroupedProductsByInvoices($object)
    {
        $order         = Mage::getModel('sales/order')->load($object['order_id']);
        $orderInvoices = $order->getInvoiceCollection();

        $result = array();

        foreach ($order->getAllItems() as $orderItem) {

            if (!$orderItem->isDummy() && !$orderItem->getQtyToRefund()) {
                continue;
            }

            $qty = $this->getQtyForOrderItemId($object, $orderItem->getId());

            if ($qty == 0) {
                continue;
            }

            $remainingQty = $qty;

            foreach ($orderInvoices as $invoice) {

                if (!$remainingQty) {
                    continue;
                }

                // refund shipping fees  for first invoice
                if (isset($object['refund_shipping_fees']) && $object['refund_shipping_fees']) {
                    $result[$invoice->getId()]['refund_shipping_amount'] = $object['refund_shipping_amount'];
                    unset($object['refund_shipping_fees']); // dont refund shipping fore next invoices
                }

                $invoiceQtyRefundLimit = $this->getInvoiceQtysRefundLimits($orderItem, $invoice);

                if ($invoiceQtyRefundLimit) {
                    $invoiceRefundQty                     = min($invoiceQtyRefundLimit, $remainingQty);
                    $result[$invoice->getId()]['items'][] = array(
                        'item' => $orderItem,
                        'qty'  => $invoiceRefundQty
                    );
                    $result[$invoice->getId()]['refund']  = $object['refund'];
                    $result[$invoice->getId()]['fee']     = $object['fee'];

                    $remainingQty -= max($invoiceRefundQty, 0);
                }
            }

            if ($remainingQty) {
                Mage::throwException($this->__("Cannot refund qty %s of %s", $qty, $orderItem->getName()));
            }
        }

        return $result;
    }

    /**
     * Return qty refund limit for given invoice
     *
     * @param order_item
     * @param invoice
     *
     * @return qty
     */
    protected function getInvoiceQtysRefundLimits($orderItem, $invoice)
    {

        $invoiceItem = $this->getInvoiceItemForOrderItem($orderItem, $invoice);

        if (!$invoiceItem) {
            return 0;
        }

        $invoiceQtyRefunded = $this->getInvoiceQtysRefunded($orderItem, $invoice);

        return $invoiceItem->getQty() - $invoiceQtyRefunded;
    }

    /**
     * Return invice_item for given order_item
     *
     * @param order_item
     * @param invoice
     *
     * @return invoice_item or false
     */
    protected function getInvoiceItemForOrderItem($orderItem, $invoice)
    {
        foreach ($invoice->getAllItems() as $invoiceItem) {
            if ($invoiceItem->getOrderItemId() == $orderItem->getId()) {
                return $invoiceItem;
            }
        }

        return false;
    }

    /**
     * Return already refunded item for given invoice
     *
     * @param order_item
     * @param invoice
     *
     * @return qty
     */
    protected function getInvoiceQtysRefunded($orderItem, $invoice)
    {
        $qtyRefunded = 0;

        foreach ($invoice->getOrder()->getCreditmemosCollection() as $createdCreditmemo) {
            if ($createdCreditmemo->getState() != Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED
                && $createdCreditmemo->getInvoiceId() == $invoice->getId()
            ) {
                foreach ($createdCreditmemo->getAllItems() as $createdCreditmemoItem) {
                    $orderItemId = $createdCreditmemoItem->getOrderItem()->getId();
                    if ($orderItem->getId() == $orderItemId) {
                        $qtyRefunded += $createdCreditmemoItem->getQty();
                    }
                }
            }
        }

        return $qtyRefunded;
    }

    /**
     * Check if credit memo request contains products
     *
     * @param unknown_type $object
     *
     * @return unknown
     */
    protected function hasProducts($object)
    {
        $retour = false;
        foreach ($object['products'] as $item) {
            if ($item['qty'] > 0)
                $retour = true;
        }

        return $retour;
    }

    /**
     * Return qty to refund for productid
     *
     * @param unknown_type $object
     * @param              $productId
     *
     * @return int
     */
    protected function getQtyForOrderItemId($object, $orderItemId)
    {
        $retour   = 0;
        $products = $object['products'];
        foreach ($products as $key => $value) {
            if ($value['order_item_id'] == $orderItemId)
                $retour = $value['qty'];
        }

        return $retour;
    }


    /**
     * Return qty to refund for productid
     *
     * @param unknown_type $object
     * @param              $productId
     *
     * @return int
     */
    protected function getPriceForOrderItemId($object, $orderItemId)
    {
        $retour   = 0;
        $products = $object['products'];
        foreach ($products as $key => $value) {
            if ($value['order_item_id'] == $orderItemId)
                $retour = $value['price'];
        }

        return $retour;
    }

}