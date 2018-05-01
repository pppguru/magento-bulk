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
class MDN_Orderpreparation_Helper_Documents extends Mage_Core_Helper_Abstract {

    public function generateDocumentsPdf() {
        try {
            $pdf = new Zend_Pdf();

            //list orders
            $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
            $operatorId = mage::helper('Orderpreparation')->getOperator();
            $collection = mage::getModel('Orderpreparation/ordertoprepare')
                            ->getCollection()
                            ->addFieldToFilter('preparation_warehouse', $preparationWarehouseId)
                            ->addFieldToFilter('user', $operatorId)
                            ->setOrder('order_id', 'asc');

            //Add comments
            if (Mage::getStoreConfig('orderpreparation/download_document_options/print_comments') == 1) {
                $CommentsModel = Mage::getModel('Orderpreparation/Pdf_SelectedOrdersComments');
                $pdf = $CommentsModel->getPdf($collection);

                for ($i = 0; $i < count($CommentsModel->pages); $i++){
                    $pdf->pages[] = $CommentsModel->pages[$i];
                }
            }

            //Add shipments & invoices
            foreach ($collection as $item) {

                //add shipment
                if (Mage::getStoreConfig('orderpreparation/download_document_options/print_shipments') == 1) {
                    $ShipmentId = $item->getshipment_id();
                    if ($ShipmentId !=  null && $ShipmentId != '') {
                        $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($ShipmentId);
                        if ($shipment->getId()) {

                            $ShipmentPdfModel = Mage::getModel('sales/order_pdf_shipment');
                            $ShipmentPdfModel->pdf = $pdf;
                            $ShipmentPdfModel = $ShipmentPdfModel->getPdf(array($shipment));

                            for ($i = 0; $i < count($ShipmentPdfModel->pages); $i++) {
                                $pdf->pages[] = $ShipmentPdfModel->pages[$i];
                            }
                        }
                    }
                }

                //Add invoice
                if (Mage::getStoreConfig('orderpreparation/download_document_options/print_invoices') == 1) {
                    $InvoiceId = $item->getinvoice_id();
                    if ($InvoiceId !=  null && $InvoiceId != '') {
                        $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($InvoiceId);
                        if ($invoice->getId()) {
                            //define printing count
                            $printingCount = 1;
                            if (mage::getStoreConfig('orderpreparation/download_document_options/print_invoice_twice_if_taxless') == 1) {
                                if ($invoice->getbase_tax_amount() == 0)
                                    $printingCount++;
                            }
                            $payment = $invoice->getOrder()->getPayment();
                            if($payment !== null) {
                                $currentPaymentMethod = $payment->getMethodInstance()->getcode();
                                $PaymentMethodsTwice = mage::getStoreConfig('orderpreparation/download_document_options/print_invoice_twice_if_payment_method');
                                $pos = strpos($PaymentMethodsTwice, $currentPaymentMethod);
                                if (!($pos === false))
                                    $printingCount++;

                                //Add to pdf
                                for ($printingNumber = 0; $printingNumber < $printingCount; $printingNumber++) {
                                    $InvoicePdfModel = Mage::getModel('sales/order_pdf_invoice');
                                    $InvoicePdfModel->pdf = $pdf;
                                    $InvoicePdfModel = $InvoicePdfModel->getPdf(array($invoice));

                                    for ($i = 0; $i < count($InvoicePdfModel->pages); $i++) {
                                        $pdf->pages[] = $InvoicePdfModel->pages[$i];
                                    }
                                }
                            }
                        }
                    }
                }

                //Add preparation PDF
                if (Mage::getStoreConfig('orderpreparation/download_document_options/print_preparation') == 1) {
                    $order = mage::getModel('sales/order')->load($item->getorder_id());
                    $preparationModel = mage::getModel('Orderpreparation/Pdf_OrderPreparationCommentsPdf');
                    $preparationModel->pdf = $pdf;
                    $preparationModel = $preparationModel->getPdfWithMode($order, MDN_Orderpreparation_Model_Pdf_OrderPreparationCommentsPdf::MODE_ORDER_PREPRATION_SELECTED_TAB);
                }

                //Dispatch event to allow other extensions to add customer pages to PDF
                Mage::dispatchEvent('orderpreparation_order_added_to_download_document', array('pdf' => $pdf, 'order_to_prepare' => $item));
            }

            return $pdf;
        } catch (Exception $ex) {
            die("Erreur lors de la g?n?ration du PDF de facture: " . $ex->getMessage() . '<p>' . $ex->getTraceAsString());
        }
    }

}