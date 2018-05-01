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


class AW_Helpdesk3_Adminhtml_Customization_PriorityController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/customization/priority');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Customization'));
        $this->_title($this->__('Manage Priorities'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3')
        ;
        return $this;
    }

    protected function _initPriority()
    {
        $priority = Mage::getModel('aw_hdu3/ticket_priority');
        $priorityId  = (int) $this->getRequest()->getParam('id', null);
        if ($priorityId) {
            $priority->load($priorityId);
        }
        if (null !== Mage::getSingleton('adminhtml/session')->getHDUPriorityFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getHDUPriorityFormData())
        ) {
            $priority->addData(Mage::getSingleton('adminhtml/session')->getHDUPriorityFormData());
            Mage::getSingleton('adminhtml/session')->setHDUPriorityFormData(null);
        }
        Mage::register('current_priority', $priority);
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
        $this->_initPriority();
        /** @var AW_Helpdesk3_Model_Ticket_Priority $priority */
        $priority = Mage::registry('current_priority');
        if (null === $priority->getId()) {
            $this->_title($this->__('New Ticket Priority'));
        } else {
            $this->_title($priority->getTitle(Mage::app()->getStore()->getId()));
        }
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($formData = $this->getRequest()->getPost()) {
            $this->_initPriority();
            $priority = Mage::registry('current_priority');
            try {
                $priority
                    ->addData($formData)
                    ->save()
                    ->setLabelValues($formData['label_values'])
                ;
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket Priority successfully saved'));
                Mage::getSingleton('adminhtml/session')->setHDUPriorityFormData(null);
                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id'  => $priority->getId(),
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setHDUPriorityFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                        'id' => $priority->getId(),
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $this->_initPriority();
        $priority = Mage::registry('current_priority');
        try {
            if ($priority->getIsSystem()) {
                throw new Exception($this->__('Cannot delete system priority'));
            }
            $ticketCollection = Mage::getModel('aw_hdu3/ticket')->getCollection();
            $ticketCollection->addFilterByPriority($priority->getId());
            if($ticketCollection->getSize() === 0) {
                $priority->setStatus(AW_Helpdesk3_Model_Source_Status::DELETED_VALUE);
                $priority->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Ticket priority has been successfully deleted')
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__(
                        'You can delete ticket priority only if there are no tickets with this property value.'
                        . ' Please change priority of such tickets first.'
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
        $priorityIds = $this->getRequest()->getParam('priorityIds', null);
        $status = $this->getRequest()->getParam('status', null);
        if (null === $status) {
            $this->_redirectReferer();
        }
        try {
            if (!is_array($priorityIds)) {
                throw new Mage_Core_Exception($this->__('Invalid ticket priority id(s)'));
            }

            $updatedCount = 0;
            $failedCount = 0;
            foreach ($priorityIds as $id) {
                $priority = Mage::getModel('aw_hdu3/ticket_priority')->load($id);
                if($priority->getIsSystem() && $status == AW_Helpdesk3_Model_Source_Status::DISABLED_VALUE) {
                    $failedCount++;
                    continue;
                }
                $priority
                    ->setStatus($status)
                    ->save()
                ;
                $updatedCount++;
            }
            if ($updatedCount) {
                if ($updatedCount == 1) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('%d ticket priority has been successfully updated.', $updatedCount)
                    );
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('%d ticket priorities have been successfully updated.', $updatedCount)
                    );
                }
            }
            if ($failedCount) {
                if ($failedCount == 1) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        $this->__('%d ticket priority has not been updated. The system priorities can???t be disabled', $failedCount)
                    );
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(
                        $this->__('%d ticket priorities have not been updated. The system priorities can???t be disabled', $failedCount)
                    );
                }
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }
}
