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


class AW_Helpdesk3_Adminhtml_DepartmentController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/department');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Departments'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3')
        ;
        return $this;
    }

    protected function _initDepartment()
    {
        $department = Mage::getModel('aw_hdu3/department');
        $departmentId = (int) $this->getRequest()->getParam('id', null);
        if ($departmentId) {
            $department->load($departmentId);
            $department->setAgentIds($department->getAgentCollection()->getAllIds());
        } else {
            $department->setData('notification',
                array(
                    'sender' => 'general',
                    'to_admin_new_ticket_email' => 'aw_hdu3_to_admin_new_ticket_email',
                    'to_customer_new_ticket_email' => 'aw_hdu3_to_customer_new_ticket_email',
                    'to_customer_new_ticket_by_admin_email' => 'aw_hdu3_to_customer_new_ticket_by_admin_email',
                    'to_admin_new_reply_email' => 'aw_hdu3_to_admin_new_reply_email',
                    'to_customer_new_reply_email' => 'aw_hdu3_to_customer_new_reply_email',
                    'to_primary_agent_reassign_email' => 'aw_hdu3_to_primary_agent_reassign_email',
                    'to_new_assignee_reassign_email' => 'aw_hdu3_to_new_assignee_reassign_email',
                    'to_customer_ticket_changed' => 'aw_hdu3_to_customer_ticket_changed',
                )
            );
            $department->setIsAllowAttachment(AW_Helpdesk3_Model_Source_Yesno::YES_VALUE);
        }
        if (null !== Mage::getSingleton('adminhtml/session')->getHDUDepartmentFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getHDUDepartmentFormData())
        ) {
            $department->addData(Mage::getSingleton('adminhtml/session')->getHDUDepartmentFormData());
            Mage::getSingleton('adminhtml/session')->setHDUDepartmentFormData(null);
        }
        Mage::register('current_department', $department);
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
        $this->_initDepartment();
        /** @var AW_Helpdesk3_Model_Department $department */
        $department = Mage::registry('current_department');
        if (null === $department->getId()) {
            $this->_title($this->__('New Department'));
        } else {
            $this->_title($department->getTitle());
        }
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($formData = $this->getRequest()->getPost()) {
            $this->_initDepartment();
            $department = Mage::registry('current_department');
            try {
                if (!array_key_exists('store_ids', $formData)) {
                    $formData['store_ids']='';
                }
                $department
                    ->addData($formData)
                    ->save();
                if (array_key_exists('notification', $formData)) {
                    $department
                        ->getEmailNotification()
                        ->addData($formData['notification'])
                        ->setDepartmentId($department->getId())
                        ->save();
                }
                $permissionData = array(
                    'department_ids' => array(),
                    'admin_role_ids' => array()
                );
                if (array_key_exists('permission', $formData)) {
                    $permissionData = array_merge($permissionData, $formData['permission']);
                }
                $department
                    ->getPermission()
                    ->addData($permissionData)
                    ->setDepartmentId($department->getId())
                    ->save()
                ;
                if (array_key_exists('gateway', $formData) && array_key_exists('email', $formData['gateway'])) {
                    $existGateway = Mage::getModel('aw_hdu3/gateway')->loadByEmail($formData['gateway']['email']);
                    if (null !== $existGateway->getId()
                        && $existGateway->getId() != $department->getGateway()->getId()
                    ) {
                        throw new Exception('This gateway email already used.');
                    }
                    $department
                        ->getGateway()
                        ->addData($formData['gateway'])
                        ->setDepartmentId($department->getId())
                        ->save()
                    ;
                }
                if (array_key_exists('gateway', $formData) && !$formData['gateway']['is_active']) {
                    $department->getGateway()->delete();
                }
                if (array_key_exists('agent_ids', $formData)) {
                    if ($formData['primary_agent_id'] && !in_array($formData['primary_agent_id'], $formData['agent_ids'])) {
                        array_push($formData['agent_ids'], $formData['primary_agent_id']);
                    }
                    $department->addDepartmentAgents($formData['agent_ids']);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Department successfully saved'));
                Mage::getSingleton('adminhtml/session')->setHDUDepartmentFormData(null);
                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id'  => $department->getId(),
                            'active_tab' => $this->getRequest()->getParam('active_tab', null)
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aw_hdu3')->__($e->getMessage()));
                Mage::getSingleton('adminhtml/session')->setHDUDepartmentFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                        'id'  => $department->getId(),
                        'active_tab' => $this->getRequest()->getParam('active_tab', null)
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $this->_initDepartment();
        $department = Mage::registry('current_department');
        try {
            $ticketCollection = Mage::getModel('aw_hdu3/ticket')->getCollection();
            $ticketCollection->addNotArchivedFilter();
            $ticketCollection->addFilterByDepartmentId($department->getId());
            if($ticketCollection->getSize() === 0) {
                $department->setStatus(AW_Helpdesk3_Model_Source_Status::DELETED_VALUE);
                $department->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Department has been successfully deleted')
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__(
                        'You can delete department only if there are no tickets assigned to it.'
                        . ' Please assign such tickets to other department first.'
                    )
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/list');
    }

    public function testConnectionAction()
    {
        $response = array(
            'success' => false,
            'msg'     => $this->__('Oops, something went wrong. Please check gateway settings and try again.')
        );
        $params = $this->getRequest()->getParams();
        try {
            Mage::getModel('aw_hdu3/gateway')->addData($params['gateway'])->testConnection();
            $response = array(
                'success' => true,
                'msg'     => $this->__('Success.')
            );
        } catch (Exception $e) {
            $response['msg'] = $this->__($e->getMessage());
        }
        $this->getResponse()->setBody(Zend_Json::encode($response));
    }
}
