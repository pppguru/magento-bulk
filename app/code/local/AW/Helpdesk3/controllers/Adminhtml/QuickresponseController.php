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


class AW_Helpdesk3_Adminhtml_QuickresponseController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/quick_response');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Quick responses'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3')
        ;
        return $this;
    }

    protected function _initTemplate()
    {
        $template = Mage::getModel('aw_hdu3/template');
        $templateId  = (int) $this->getRequest()->getParam('id', null);
        if ($templateId) {
            $template->load($templateId);
        }
        if (null !== Mage::getSingleton('adminhtml/session')->getHDUTemplateFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getHDUTemplateFormData())
        ) {
            $template->addData(Mage::getSingleton('adminhtml/session')->getHDUTemplateFormData());
            Mage::getSingleton('adminhtml/session')->setHDUTemplateFormData(null);
        }
        Mage::register('current_template', $template);
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $this->_initAction()
            ->renderLayout()
        ;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initAction();
        $this->_initTemplate();
        /** @var AW_Helpdesk3_Model_Template $template */
        $template = Mage::registry('current_template');
        if (null === $template->getId()) {
            $this->_title($this->__('New Quick Response'));
        } else {
            $this->_title($template->getTitle());
        }
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($formData = $this->getRequest()->getPost()) {
            $this->_initTemplate();
            $template = Mage::registry('current_template');
            try {
                $template
                    ->addData($formData)
                    ->save()
                ;
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Template successfully saved'));
                Mage::getSingleton('adminhtml/session')->setHDUTemplateFormData(null);
                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id'  => $template->getId(),
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setHDUTemplateFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                        'id' => $template->getId(),
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $this->_initTemplate();
        $template = Mage::registry('current_template');
        try {
            $template->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Template has been successfully deleted')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/list');
    }

    public function massDeleteAction()
    {
        $templateIds = $this->getRequest()->getParam('templateIds', null);
        try {
            if (!is_array($templateIds)) {
                throw new Mage_Core_Exception($this->__('Invalid template id(s)'));
            }
            foreach ($templateIds as $id) {
                $template = Mage::getModel('aw_hdu3/template')->load($id);
                $template->delete();
            }
            if (count($templateIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d template has been successfully deleted', count($templateIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d templates have been successfully deleted', count($templateIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }
}
