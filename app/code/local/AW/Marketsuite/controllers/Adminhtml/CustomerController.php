<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('marketsuite/customers');
    }

    public function indexAction()
    {
        $this->_title($this->__('MSS'))->_title($this->__('Customers'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('marketsuite');
        $this->renderLayout();
    }

    public function viewAction()
    {
        Mage::helper('marketsuite')->setBackUrl($this->getUrl('*/*/index'));
        return $this->_redirect(
            'adminhtml/customer/edit',
            array(
                 'id'                                            => $this->getRequest()->getParam('id'),
                 AW_Marketsuite_Helper_Data::USE_AW_BACKURL_FLAG => 1,
            )
        );
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'customers.csv';
        $content = $this->getLayout()->createBlock('marketsuite/adminhtml_customer_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'customers.xml';
        $content = $this->getLayout()->createBlock('marketsuite/adminhtml_customer_grid')->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massMoveAction()
    {
        $customerIds = $this->getRequest()->getParam('customer_id');
        $group = $this->getRequest()->getParam('group');
        if (!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('marketsuite')->__('Please select customer(s)')
            );
        } else {
            try {
                foreach ($customerIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setGroupId($group)->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('marketsuite')->__('Total of %d record(s) were successfully updated',count($customerIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    $e->getMessage()
                );
            }
        }
        $this->_redirect('*/*/index');
    }
}