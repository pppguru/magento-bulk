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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Adminhtml_Rejecting_EmailController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/rejecting/email');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Email Rejecting'));
        $this->_title($this->__('Rejected Emails'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3/rejecting/rejectedemails')
        ;
        return $this;
    }

    protected function _initEmail()
    {
        $emailId  = (int) $this->getRequest()->getParam('id', null);
        $emailModel = Mage::getModel('aw_hdu3/gateway_mail');
        try {
            $emailModel->load($emailId);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $emailModel;
    }

    protected function indexAction()
    {
        $this->_forward('list');
    }

    protected function listAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function markAsUnprocessedAction()
    {
        $emailModel = $this->_initEmail();
        if(null === $emailModel->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Invalid email id'));
            $this->_redirect('*/*/list');
            return $this;
        }
        try {
            $emailModel
                ->setRejectPatternId(null)
                ->setStatus(AW_Helpdesk3_Model_Gateway_Mail::STATUS_UNPROCESSED)
                ->save()
            ;
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Email has been successfully marked as unprocessed')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/list');
    }

    public function deleteAction()
    {
        $emailModel = $this->_initEmail();
        if(null === $emailModel->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Invalid email id'));
            $this->_redirect('*/*/list');
            return $this;
        }
        try {
            $emailModel->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Email has been successfully deleted')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/list');
    }

    public function massMarkAsUnprocessedAction()
    {
        $emailIds = $this->getRequest()->getParam('emailIds', null);
        try {
            if (!is_array($emailIds)) {
                throw new Mage_Core_Exception($this->__('Invalid email id(s)'));
            }

            foreach ($emailIds as $id) {
                $emailModel = Mage::getModel('aw_hdu3/gateway_mail')->load($id);
                $emailModel
                    ->setRejectPatternId(null)
                    ->setStatus(AW_Helpdesk3_Model_Gateway_Mail::STATUS_UNPROCESSED)
                    ->save()
                ;
            }
            if (count($emailIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d email has been successfully marked as unprocessed', count($emailIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d emails have been successfully marked as unprocessed', count($emailIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $emailIds = $this->getRequest()->getParam('emailIds', null);
        try {
            if (!is_array($emailIds)) {
                throw new Mage_Core_Exception($this->__('Invalid email id(s)'));
            }

            foreach ($emailIds as $id) {
                $emailModel = Mage::getModel('aw_hdu3/gateway_mail')->load($id);
                $emailModel->delete();
            }
            if (count($emailIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d email has been successfully deleted', count($emailIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d emails have been successfully deleted', count($emailIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }
}