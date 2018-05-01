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
 * @author     : Sylvain SALERNO
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Block_Admin_SupplierReturn_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {

        parent::__construct();
        $this->setId('supplierreturn_tab');
        $this->setDestElementId('supplierreturn_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');

    }

    protected function _beforeToHtml()
    {

        $retour = array();

        //NEW
        $newRetour               = Mage::helper('ProductReturn/SupplierReturn')->getNewSupplierReturns();
        $retour['new']['qty']    = $newRetour->count();
        $retour['new']['amount'] = 0;
        foreach ($newRetour as $ret) {
            $products = $ret->getProducts();
            foreach ($products as $product) {
                $retour['new']['amount'] += $product->getrsrp_purchase_price();
            }
        }

        //INQUIRY
        $inquiryRetour               = Mage::helper('ProductReturn/SupplierReturn')->getInquirySupplierReturns();
        $retour['inquiry']['qty']    = $inquiryRetour->count();
        $retour['inquiry']['amount'] = 0;
        foreach ($inquiryRetour as $ret) {
            $products = $ret->getProducts();
            foreach ($products as $product) {
                $retour['inquiry']['amount'] += $product->getrsrp_purchase_price();
            }
        }
        //SENT
        $sentRetour               = Mage::helper('ProductReturn/SupplierReturn')->getSentSupplierReturns();
        $retour['sent']['qty']    = $sentRetour->count();
        $retour['sent']['amount'] = 0;
        foreach ($sentRetour as $ret) {
            $products = $ret->getProducts();
            foreach ($products as $product) {
                $retour['sent']['amount'] += $product->getrsrp_purchase_price();
            }
        }
        //COMPLETE
        $completeRetour               = Mage::helper('ProductReturn/SupplierReturn')->getCompleteSupplierReturns();
        $retour['complete']['qty']    = $completeRetour->count();
        $retour['complete']['amount'] = 0;
        foreach ($completeRetour as $ret) {
            $products = $ret->getProducts();
            foreach ($products as $product) {
                $retour['complete']['amount'] += $product->getrsrp_purchase_price();
            }
        }

        $this->addTab('new', array(
            'label'   => Mage::helper('ProductReturn')->__('New') . ' (' . $retour['new']['qty'] . ' return, ' . $retour['new']['amount'] . ' ' . mage::getStoreConfig('currency/options/base') . ')',
            'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabNew')->toHtml(),
            'active'  => true
        ));

        $this->addTab('Inquiry', array(
            'label'   => Mage::helper('ProductReturn')->__('Inquiry') . ' (' . $retour['inquiry']['qty'] . ' return, ' . $retour['inquiry']['amount'] . ' ' . mage::getStoreConfig('currency/options/base') . ')',
            'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabInquiry')->toHtml()
        ));

        $this->addTab('sent_to_supplier', array(
            'label'   => Mage::helper('ProductReturn')->__('Sent to supplier') . ' (' . $retour['sent']['qty'] . ' return, ' . $retour['sent']['amount'] . ' ' . mage::getStoreConfig('currency/options/base') . ')',
            'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabSentToSupplier')->toHtml()
        ));

        $this->addTab('Complete', array(
            'label'   => Mage::helper('ProductReturn')->__('Complete') . ' (' . $retour['complete']['qty'] . ' return, ' . $retour['complete']['amount'] . ' ' . mage::getStoreConfig('currency/options/base') . ')',
            'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Tabs_TabComplete')->toHtml()
        ));

        return parent::_beforeToHtml();
    }

}