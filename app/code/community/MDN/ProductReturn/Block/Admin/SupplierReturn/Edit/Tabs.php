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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order view tabs
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_ProductReturn_Block_Admin_SupplierReturn_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('supplierreturn_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ProductReturn')->__('Supplier Return Edit'));

    }

    protected function _beforeToHtml()
    {
        $rsrId = mage::app()->getRequest()->getParam('rsr_id');

        $this->addTab('general', array(
            'label'   => Mage::helper('ProductReturn')->__('General'),
            'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Edit_Tab_General')->setTemplate('ProductReturn/SupplierReturn/Edit/Tabs/General.phtml')->toHtml(),
            'active'  => true
        ));

        if ($rsrId) {
            $this->addTab('products', array(
                'label'   => Mage::helper('ProductReturn')->__('Products'),
                'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Edit_Tab_Products')->setTemplate('ProductReturn/SupplierReturn/Edit/Tabs/Products.phtml')->toHtml(),
            ));

            $this->addTab('history', array(
                'label'   => Mage::helper('ProductReturn')->__('History'),
                'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Edit_Tab_History')->setTemplate('ProductReturn/SupplierReturn/Edit/Tabs/History.phtml')->toHtml(),
            ));

            $this->addTab('senttosupplier', array(
                'label'   => Mage::helper('ProductReturn')->__('Sent to supplier'),
                'content' => $this->getLayout()->createBlock('ProductReturn/Admin_SupplierReturn_Edit_Tab_SentToSupplier')->setTemplate('ProductReturn/SupplierReturn/Edit/Tabs/SentToSupplier.phtml')->toHtml(),
            ));

            //raise event to allow other extension to add tabs
            //Mage::dispatchEvent('productreturn_edit_create_tabs', array('tab' => $this, 'rsr' => $this->getRsr(), 'layout' => $this->getLayout()));
        }

        //select tab
        /*
        $defaultTab = $this->getRequest()->getParam('tab');
        if ($defaultTab == null)
            $defaultTab = 'general';
        $this->setActiveTab($defaultTab);
    	    */

        return parent::_beforeToHtml();
    }

    public function getRsr()
    {
        return mage::registry('current_rsr');
    }

}
