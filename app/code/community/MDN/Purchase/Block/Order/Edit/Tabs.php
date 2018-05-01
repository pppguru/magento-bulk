<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * admin product edit tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_Purchase_Block_Order_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    private $_purchaseOrder = null;

    public function __construct() {
        parent::__construct();
        $this->setId('purchase_order_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml() {
        $product = $this->getProduct();

        $this->addTab('tab_info', array(
            'label' => Mage::helper('purchase')->__('Summary'),
            'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_Info')->toHtml(),
        ));

        $this->addTab('tab_products', array(
            'label' => Mage::helper('purchase')->__('Products'),
            'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_ProductsGrid')->setOrderId($this->getPurchaseOrder()->getId())->toHtml()
            . "<script>persistantProductGrid = new persistantGridControl(ProductsGridJsObject, 'order_product_log', 'pop_qty_', updateOrderProductInformation);</script>",
        ));

        if ($this->getPurchaseOrder()->currentUserCanEdit())
        {
            if ($this->getPurchaseOrder()->getpo_status() != MDN_Purchase_Model_Order::STATUS_COMPLETE)
            {
                if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_orders/tabs/add_products'))
                {
                    $this->addTab('tab_add_products', array(
                        'label' => Mage::helper('purchase')->__('Add Products'),
                        'url' => $this->getUrl('*/*/ProductSelectionGrid', array('_current' => true, 'po_num' => $this->getPurchaseOrder()->getId())),
                        'class' => 'ajax',
                    ));
                }

                if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_orders/tabs/import_products'))
                {
                     $this->addTab('tab_import_products', array(
                         'label'     => Mage::helper('AdvancedStock')->__('Import products'),
                         'content'   => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_ImportProducts')->toHtml(),
                     ));
                }
            }
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_orders/tabs/deliveries'))
        {
            $this->addTab('tab_deliveries', array(
                'label' => Mage::helper('purchase')->__('Deliveries'),
                'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_Deliveries')->setOrderId($this->getPurchaseOrder()->getId())->toHtml()
                . "<script>persistantDeliveryGrid = new persistantGridControl(ProductsDeliveryGridJsObject, 'delivery_log', 'delivery_qty_', null);</script>",
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_orders/tabs/serials'))
        {
            $this->addTab('tab_serials', array(
                'label' => Mage::helper('purchase')->__('Product serial numbers'),
                'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_Serials')->setOrderId($this->getPurchaseOrder()->getId())->toHtml(),
            ));
        }
        
        if ($this->getPurchaseOrder()->currentUserCanEdit())
        {
            if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_orders/tabs/supplier_notification'))
            {
                if ($this->getPurchaseOrder()->getpo_status() != MDN_Purchase_Model_Order::STATUS_COMPLETE) {
                    $this->addTab('tab_send_to_supplier', array(
                        'label' => Mage::helper('purchase')->__('Supplier notification'),
                        'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_SendToSupplier')->setOrderId($this->getPurchaseOrder()->getId())->toHtml(),
                    ));
                }
            }
        }

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_orders/tabs/history'))
        {
            $this->addTab('tab_history', array(
                    'label' => Mage::helper('purchase')->__('History'),
                    'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_History')->setOrderId($this->getPurchaseOrder()->getId())->toHtml(),
                ));
        }

        /*if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_orders/tabs/accounting'))
        {
            $this->addTab('tab_accounting', array(
                'label' => Mage::helper('purchase')->__('Accounting'),
                'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_accounting')->setOrderId($this->getPurchaseOrder()->getId())->toHtml(),
            ));
        }*/

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/supplier_invoices/edit'))
        {
            $this->addTab('tab_supplier_invoices', array(
                'label' => Mage::helper('purchase')->__('Supplier Invoices'),
                'content' => $this->getLayout()->createBlock('Purchase/Order_Edit_Tabs_SupplierInvoice')->setOrderId($this->getPurchaseOrder()->getId())->toHtml(),
            ));
        }

        $TaskCount = 0;
        $gridBlock = $this->getLayout()
                ->createBlock('Organizer/Task_Grid')
                ->setEntityType('purchase_order')
                ->setEntityId($this->getPurchaseOrder()->getId())
                ->setShowTarget(false)
                ->setShowEntity(false)
                ->setTemplate('Organizer/Task/List.phtml');

        $content = $gridBlock->toHtml();

        $TaskCount = $gridBlock->getCollection()->getSize();
        $this->addTab('purchase_order_organizer', array(
            'label' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
            'title' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
            'content' => $content,
        ));

        //dispatch event to allow other extension to add their own tabs
        Mage::dispatchEvent('purchase_order_edit_create_tabs', array('tab' => $this, 'purchase_order' => $this->getPurchaseOrder(), 'layout' => $this->getLayout()));

        //set active tab
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
            $defaultTab = 'tab_info';
        $this->setActiveTab($defaultTab);

        return parent::_beforeToHtml();
    }

    /**
     * Retrive product object from object if not from registry
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getPurchaseOrder() {
        if ($this->_purchaseOrder == null) {
            $this->_purchaseOrder = mage::getModel('Purchase/Order')->load($this->getRequest()->getParam('po_num'));
        }
        return $this->_purchaseOrder;
    }

}
