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


class AW_Helpdesk3_Adminhtml_Rejecting_PatternController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/rejecting/pattern');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Email Rejecting'));
        $this->_title($this->__('Manage Patterns'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3/rejecting/managepatterns')
        ;
        return $this;
    }

    protected function _initPattern()
    {
        $pattern = Mage::getModel('aw_hdu3/gateway_mail_rejectPattern');
        $patternId  = (int) $this->getRequest()->getParam('id', null);
        if ($patternId) {
            $pattern->load($patternId);
        }
        if (null !== Mage::getSingleton('adminhtml/session')->getHDUPatternFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getHDUPatternFormData())
        ) {
            $pattern->addData(Mage::getSingleton('adminhtml/session')->getHDUPatternFormData());
            Mage::getSingleton('adminhtml/session')->setHDUPatternFormData(null);
        }
        Mage::register('current_pattern', $pattern);
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

    protected function newAction()
    {
        $this->_forward('edit');
    }

    protected function editAction()
    {
        $this->_initAction();
        $this->_initPattern();

        $pattern = Mage::registry('current_pattern');
        if (null === $pattern->getId()) {
            $this->_title($this->__('New Pattern'));
        } else {
            $this->_title($pattern->getTitle());
        }
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($formData = $this->getRequest()->getPost()) {
            $this->_initPattern();
            $pattern = Mage::registry('current_pattern');
            try {
                $pattern
                    ->addData($formData)
                    ->save()
                ;
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Pattern successfully saved'));
                Mage::getSingleton('adminhtml/session')->setHDUPatternFormData(null);
                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id'  => $pattern->getId(),
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setHDUPatternFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                        'id' => $pattern->getId(),
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $this->_initPattern();
        $pattern = Mage::registry('current_pattern');
        try {
            $pattern->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Pattern has been successfully deleted')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/list');
    }

    public function massStatusAction()
    {
        $patternIds = $this->getRequest()->getParam('patternIds', null);
        $status = $this->getRequest()->getParam('status', null);
        if (!$status) {
            $this->_redirectReferer();
        }
        try {
            if (!is_array($patternIds)) {
                throw new Mage_Core_Exception($this->__('Invalid ticket pattern id(s)'));
            }

            foreach ($patternIds as $id) {
                $pattern = Mage::getModel('aw_hdu3/gateway_mail_rejectPattern')->load($id);
                $pattern
                    ->setIsActive($status)
                    ->save()
                ;
            }
            if (count($patternIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d pattern has been successfully updated.', count($patternIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d patterns have been successfully updated.', count($patternIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $patternIds = $this->getRequest()->getParam('patternIds', null);
        try {
            if (!is_array($patternIds)) {
                throw new Mage_Core_Exception($this->__('Invalid pattern id(s)'));
            }

            foreach ($patternIds as $id) {
                $pattern = Mage::getModel('aw_hdu3/gateway_mail_rejectPattern')->load($id);
                $pattern->delete();
            }
            if (count($patternIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d pattern has been successfully deleted', count($patternIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d patterns have been successfully deleted', count($patternIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }
}