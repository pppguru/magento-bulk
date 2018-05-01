<?php

require_once ('../app/Mage.php');
session_start();
Mage::reset();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$collection = Mage::getModel('Purchase/Order')
    ->getCollection()
    ->addFieldToFilter('po_paid', array('eq' => 1));

foreach ($collection as $po){
    $supplierInvoice = Mage::getModel('Purchase/PurchaseSupplierInvoice');
    $supplierInvoice->setpsi_po_id($po->getId());
    $supplierInvoice->setpsi_invoice_id($po->getpo_supplier_order_ref());
    $supplierInvoice->setpsi_amount($po->getTotalHt());
    $supplierInvoice->setpsi_payment_date($po->getpo_payment_date());
    $supplierInvoice->setpsi_due_date($po->getInvoiceDueDate());
    $supplierInvoice->setpsi_date($po->getpo_invoice_date());
    $supplierInvoice->setpsi_status(MDN_Purchase_Model_PurchaseSupplierInvoice::STATUS_PAID);
    $supplierInvoice->setnotes("Generated from ERP accounting previous data");
    $supplierInvoice->save();
}