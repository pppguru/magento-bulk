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


class AW_Helpdesk3_Adminhtml_Customization_StatusController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/customization/status');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Customization'));
        $this->_title($this->__('Manage Statuses'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3')
        ;
        return $this;
    }

    protected function _initStatus()
    {
        $status = Mage::getModel('aw_hdu3/ticket_status');
        $statusId  = (int) $this->getRequest()->getParam('id', null);
        if ($statusId) {
            $status->load($statusId);
        }
        if (null !== Mage::getSingleton('adminhtml/session')->getHDUStatusFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getHDUStatusFormData())
        ) {
            $status->addData(Mage::getSingleton('adminhtml/session')->getHDUStatusFormData());
            Mage::getSingleton('adminhtml/session')->setHDUStatusFormData(null);
        }
        Mage::register('current_status', $status);
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
        $this->_initStatus();
        /** @var AW_Helpdesk3_Model_Ticket_Status $status */
        $status = Mage::registry('current_status');
        if (null === $status->getId()) {
            $this->_title($this->__('New Ticket Status'));
        } else {
            $this->_title($status->getTitle(Mage::app()->getStore()->getId()));
        }
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($formData = $this->getRequest()->getPost()) {
            Mage::log($formData, "1", "loddfdf.log");
            $this->_initStatus();
            $status = Mage::registry('current_status');
            try {
                $status
                    ->addData($formData)
                    ->save()
                    ->setLabelValues($formData['label_values'])
                ;
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket Status successfully saved'));
                Mage::getSingleton('adminhtml/session')->setHDUStatusFormData(null);
                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id'  => $status->getId(),
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setHDUStatusFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                        'id' => $status->getId(),
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $this->_initStatus();
        $status = Mage::registry('current_status');
        try {
            if ($status->getIsSystem()) {
                throw new Exception($this->__('Cannot delete system status'));
            }
            $ticketCollection = Mage::getModel('aw_hdu3/ticket')->getCollection();
            $ticketCollection->addFilterByStatus($status->getId());
            if($ticketCollection->getSize() === 0) {
                $status->setStatus(AW_Helpdesk3_Model_Source_Status::DELETED_VALUE);
                $status->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Ticket status has been successfully deleted')
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__(
                        'You can delete ticket status only if there are no tickets with this property value.'
                        . ' Please change status of such tickets first.'
                    )
                );
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/list');
    }

    public function massStatusAction()
    {
        $statusIds = $this->getRequest()->getParam('statusIds', null);
        $status = $this->getRequest()->getParam('status', null);
        if (null === $status) {
            $this->_redirectReferer();
        }
        try {
            if (!is_array($statusIds)) {
                throw new Mage_Core_Exception($this->__('Invalid ticket status id(s)'));
            }

            $updatedCount = 0;
            $failedCount = 0;
            foreach ($statusIds as $id) {
                $statusModel = Mage::getModel('aw_hdu3/ticket_status')->load($id);
                if($statusModel->getIsSystem() && $status == AW_Helpdesk3_Model_Source_Status::DISABLED_VALUE) {
                    $failedCount++;
                    continue;
                }
                $statusModel
                    ->setStatus($status)
                    ->save()
                ;
                $updatedCount++;
            }
            if ($updatedCount) {
                if ($updatedCount == 1) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('%d ticket status has been successfully updated.', $updatedCount)
                    );
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('%d ticket statuses have been successfully updated.', $updatedCount)
                    );
                }
            }
            if ($failedCount) {
                if ($failedCount == 1) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        $this->__('%d ticket status has not been updated. The system status can???t be disabled', $failedCount)
                    );
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(
                        $this->__('%d ticket statuses have not been updated. The system statuses can???t be disabled', $failedCount)
                    );
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }
}
