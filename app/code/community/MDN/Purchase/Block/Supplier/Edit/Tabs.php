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
class MDN_Purchase_Block_Supplier_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    private $_supplier = null;

    public function __construct() {
        parent::__construct();
        $this->setId('purchase_supplier_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('');
    }

    protected function _beforeToHtml() {
        $product = $this->getProduct();

        $this->addTab('tab_info', array(
            'label' => Mage::helper('purchase')->__('Summary'),
            'content' => $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Info')->toHtml(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_supplier/show_miscellaneous')) {
            $this->addTab('tab_misc', array(
                'label' => Mage::helper('purchase')->__('Miscellaneous'),
                'content' => $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Misc')->toHtml(),
            ));
        }

        $this->addTab('tab_products', array(
            'label' => Mage::helper('purchase')->__('Products'),
            'content' => $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Products')->setSupplierId($this->getSupplier()->getId())->toHtml(),
        ));

        $this->addTab('tab_orders', array(
            'label' => Mage::helper('purchase')->__('Orders'),
            'content' => $this->getLayout()->createBlock('Purchase/Supplier_Edit_Tabs_Orders')->setSupplierId($this->getSupplier()->getId())->toHtml(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing/purchase_supplier/show_contacts')) {
            $this->addTab('tab_contacts', array(
                'label' => Mage::helper('purchase')->__('Contacts'),
                'content' => $this->getLayout()->createBlock('Purchase/Contact_SubGrid')
                        ->setEntityType('supplier')
                        ->setEntityId($this->getSupplier()->getId())
                        ->setTemplate('Purchase/Contact/SubGrid.phtml')
                        ->toHtml(),
            ));
        }

        $TaskCount = 0;
        $gridBlock = $this->getLayout()
                ->createBlock('Organizer/Task_Grid')
                ->setEntityType('supplier')
                ->setEntityId($this->getSupplier()->getId())
                ->setShowTarget(false)
                ->setShowEntity(false)
                ->setTemplate('Organizer/Task/List.phtml');

        $content = $gridBlock->toHtml();

        $TaskCount = $gridBlock->getCollection()->getSize();
        $this->addTab('supplier_organizer', array(
            'label' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
            'title' => Mage::helper('Organizer')->__('Organizer') . ' (' . $TaskCount . ')',
            'content' => $content,
        ));

        Mage::dispatchEvent('purchase_supplier_tabs', array('tab' => $this, 'supplier' => $this->getSupplier()));

        //set active tab
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
            $defaultTab = 'tab_info';
        $this->setActiveTab($defaultTab);

        return parent::_beforeToHtml();
    }

    /**
     * 
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getSupplier() {
        if ($this->_supplier == null) {
            $this->_supplier = mage::getModel('Purchase/Supplier')->load($this->getRequest()->getParam('sup_id'));
        }
        return $this->_supplier;
    }

}
