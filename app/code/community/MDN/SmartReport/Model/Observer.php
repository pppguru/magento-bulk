<?php

class MDN_SmartReport_Model_Observer {


    public function purchase_supplier_tabs(Varien_Event_Observer $observer) {

        $tab = $observer->getEvent()->gettab();
        $supplier = $observer->getEvent()->getsupplier();

        $tab->addTab('tab_smartreport', array(
            'label' => Mage::helper('SmartReport')->getName(),
            'class' => 'ajax',
            'url' => Mage::helper('adminhtml')->getUrl('adminhtml/SmartReport_Reports/SupplierDetailAjax', array('supplier_id' => $supplier->getId())),
        ));

    }

    public function advancedstock_product_edit_create_tabs(Varien_Event_Observer $observer)
    {

        $tab = $observer->getEvent()->gettab();
        $product = $observer->getEvent()->getproduct();

        $tab->addTab('tab_smartreport', array(
            'label' => Mage::helper('SmartReport')->getName(),
            'class' => 'ajax',
            'url' => Mage::helper('adminhtml')->getUrl('adminhtml/SmartReport_Reports/SkuDetailAjax', array('product_id' => $product->getId())),
        ));

    }

}