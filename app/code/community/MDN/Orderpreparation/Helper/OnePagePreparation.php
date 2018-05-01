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
class MDN_Orderpreparation_Helper_OnePagePreparation extends Mage_Core_Helper_Abstract {

    /**
     * return orders list (filtered by carrier
     *
     * @param unknown_type $carriers
     */
    public function getOrderList($carriers) {
        $ids = mage::getModel('Orderpreparation/ordertoprepare')->getSelectedOrdersIds();

        if (mage::helper('AdvancedStock/FlatOrder')->ordersUseEavModel()) {

            $collection = Mage::getResourceModel('sales/order_collection')
                            ->addAttributeToSelect('shipping_method')
                            ->addFieldToFilter('entity_id', array('in' => $ids))
                            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
                            ->addExpressionAttributeToSelect('shipping_name',
                                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}}, " (", {{shipping_company}}, ")")',
                                    array('shipping_firstname', 'shipping_lastname', 'shipping_company'))
                            ->joinTable(
                                    mage::getModel('Purchase/Constant')->getTablePrefix() . 'order_to_prepare',
                                    'order_id=entity_id',
                                    array(
                                        'order_to_prepare_id' => 'id',
                                        'order_id' => 'order_id',
                                        'real_weight' => 'real_weight',
                                        'ship_mode' => 'ship_mode',
                                        'package_count' => 'package_count',
                                        'details' => 'details',
                                        'invoice_id' => 'invoice_id',
                                        'shipment_id' => 'shipment_id'
                                    )
                            )
                            ->setOrder('order_id', 'asc');
        } else {
            $collection = Mage::getResourceModel('sales/order_collection')
                            ->addAttributeToSelect('*')
                            ->join('sales/order_address', '`sales/order_address`.entity_id=shipping_address_id', array('shipping_name' => "concat(firstname, ' ', lastname)"))
                            ->join('Orderpreparation/ordertoprepare', 'order_id=main_table.entity_id')
                            ->setOrder('order_id', 'asc');
        }

        //add warehouse / operator condition
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();
        $collection->addFieldToFilter('preparation_warehouse', $preparationWarehouseId);
        $collection->addFieldToFilter('user', $operatorId);

        return $collection;
    }

    /**
     * Return PDF with invoice & shipment (if exists) for 1 order
     *
     * @param unknown_type $orderId
     */
    public function getPdfDocumentsForOrder($orderId) {
        //retrieve information
        $orderPreparationItem = mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');
        if (!$orderPreparationItem->getId())
            die('This order do not belong to selected orders');

        //init PDF
        $pdf = new Zend_Pdf();
        $order = mage::getModel('sales/order')->load($orderId);

        //dispatch event to allow other extensions to add additional document before invoice & shipment
        Mage::dispatchEvent('orderpreparation_before_print_order_documents', array('pdf' => $pdf, 'order' => $order));


        //add shipment to PDF
        if (Mage::getStoreConfig('orderpreparation/download_document_options/print_shipments') == 1) {
            //recupere le shipment (si existe)
            $ShipmentId = $orderPreparationItem->getshipment_id();
            if ($ShipmentId !=  null && $ShipmentId != '') {
                //recupere le shipment
                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($ShipmentId);
                if ($shipment->getId()) {
                    //Genere le pdf de la facture
                    $ShipmentPdfModel = Mage::getModel('sales/order_pdf_shipment');
                    if ($pdf != null) {
                        $ShipmentPdfModel->pdf = $pdf;
                        $ShipmentPdfModel = $ShipmentPdfModel->getPdf(array($shipment));

                        //Ajoute le pdf du shipment au pdf principal
                        for ($i = 0; $i < count($ShipmentPdfModel->pages); $i++) {
                            $pdf->pages[] = $ShipmentPdfModel->pages[$i];
                        }
                        
                    }
                    else
                        $pdf = $ShipmentPdfModel->getPdf(array($shipment));
                }
            }
        }

        //add invoice to PDF
        if (Mage::getStoreConfig('orderpreparation/download_document_options/print_invoices') == 1) {
            $InvoiceId = $orderPreparationItem->getinvoice_id();
            if ($InvoiceId !=  null && $InvoiceId != '') {
                //Recupere l'invoice
                $printingCount = 1;
                $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($InvoiceId);
                if ($invoice->getId()) {

                    //define printing count
                    if (mage::getStoreConfig('orderpreparation/download_document_options/print_invoice_twice_if_taxless') == 1) {
                        if ($invoice->getbase_tax_amount() == 0)
                            $printingCount += 3;
                    }

                    $payment = $invoice->getOrder()->getPayment();
                    if ($payment != null) {
                        $CurrentPaymentMethod = $payment->getMethodInstance()->getcode();
                        $PaymentMethodsTwice = mage::getStoreConfig('orderpreparation/download_document_options/print_invoice_twice_if_payment_method');
                        $pos = strpos($PaymentMethodsTwice, $CurrentPaymentMethod);
                        if (!($pos === false))
                            $printingCount++;
                    }
                }

                //Add to pdf
                for ($printingNumber = 0; $printingNumber < $printingCount; $printingNumber++) {
                    //Genere le pdf de la facture
                    $InvoicePdfModel = Mage::getModel('sales/order_pdf_invoice');
                    if ($pdf != null) {
                        $InvoicePdfModel->pdf = $pdf;
                        $InvoicePdfModel = $InvoicePdfModel->getPdf(array($invoice));

                        //Ajoute le pdf de la facture au pdf principal
                        $max = count($InvoicePdfModel->pages);
                        for ($i = 0; $i < $max; $i++) {
                            $pdf->pages[] = $InvoicePdfModel->pages[$i];
                        }
                    }
                    else
                        $pdf = $InvoicePdfModel->getPdf(array($invoice));
                }
            }
        }

        //dispatch event to allow other extensions to add additional document
        Mage::dispatchEvent('orderpreparation_print_order_documents', array('pdf' => $pdf, 'order' => $order));

        return $pdf;
    }

}
