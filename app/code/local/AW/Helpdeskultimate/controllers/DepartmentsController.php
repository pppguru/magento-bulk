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
 * @package    AW_Helpdeskultimate
 * @version    2.10.7
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdeskultimate_DepartmentsController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdeskultimate/departments');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Departments'));
        $this->loadLayout()
            ->_setActiveMenu('helpdeskultimate')
            ->_addBreadcrumb($this->__('Items Manager'), $this->__('Item Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('helpdeskultimate/adminhtml_departments'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function statsAction()
    {
        $block = $this->getLayout()
            ->createBlock('helpdeskultimate/adminhtml_departments_stats');

        $post = $this->getRequest()->getPost();

        if (!empty($post) && (@$post['depstats_from'] || @$post['depstats_to'])) {
            Mage::register('aw_hdu_departments_fromto_raw', array(@$post['depstats_from'], @$post['depstats_to']));

            $from = @$post['depstats_from'] ? $post['depstats_from'] : '01/01/1970';
            $to = @$post['depstats_to'] ? $post['depstats_to'] : '12/31/2100';
            $from = strtotime($from);
            $to = strtotime($to) + 23 * 3600 + 59 + 60 + 59;
            $block->addFromTo($from, $to);
        }
        $this->_initAction();
        $this->_title($this->__('Departments Statistics'));
        $this->_addContent($block)
            ->renderLayout();
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['id']) && !$data['id']) {
            unset($data['id']);
        }

        try {
            if ($data) {
                $data['email'] = $data['contact'];
                if (!isset($data['visible_on'])) {
                    $data['visible_on'] = '';
                }

                $model = Mage::getModel('helpdeskultimate/department')->setData($data);
            } else {
                throw(new Exception('No data to save transfered'));
            }

            if ($model->save()) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Department was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);


                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                } else {
                    $this->_redirect('*/*/');
                    return;
                }
            } else {
                throw(new Exception('Department wasn\'t saved'));
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
    }

    public function editAction()
    {
        $model = Mage::getModel('helpdeskultimate/department');
        if ($id = $this->getRequest()->getParam('id')) {
            if ($model->load($id)) {
                if ($model->getId()) {
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Department does not exist'));
                    $this->_redirect('*/');
                }
            }
        }

        Mage::register('department', $model);

        $this->_initAction();
        $_title = is_null($model->getId()) ? $this->__('New Department') : $model->getName();
        $this->_title($_title);
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $departmentId = $this->getRequest()->getParam('id', 0);
        if ($departmentId > 0) {
            $departmentModel = Mage::getModel('helpdeskultimate/department')->load($departmentId);
            $departmentModel->loadStats();
            if ($departmentModel->getData('total_count') > 0) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    $this->__(
                        'This department can\'t be deleted at this moment. '
                        . 'It is caused by the fact that some tickets assigned to this department. '
                        . 'You can manage its tickets <a href="%s">here</a>.',
                        Mage::helper('adminhtml')->getUrl(
                            'helpdeskultimate_admin/index/index',
                            array(
                                 'department' => $departmentModel->getId()
                            )
                        )
                    )
                );
                $this->_redirect('*/*/edit', array('id' => $departmentId));
                return;
            } else {
                try {
                    $departmentModel->delete();
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirectReferer();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Department was successfully deleted'));
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $departmentIds = $this->getRequest()->getParam('departments');

        if (!is_array($departmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            foreach ($departmentIds as $departmentKey => $id) {
                $departmentModel = Mage::getModel('helpdeskultimate/department')->load($id);
                $departmentModel->loadStats();

                if ($departmentModel->getData('total_count') > 0) {
                    unset($departmentIds[$departmentKey]);
                    Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__(
                            'Department "%s" can\'t be deleted at this moment. '
                            . 'It is caused by the fact that some tickets assigned to this department. '
                            . 'You can manage its tickets <a href="%s">here</a>.',
                            $departmentModel->getName(),
                            Mage::helper('adminhtml')->getUrl(
                                'helpdeskultimate_admin/index/index',
                                array(
                                     'department' => $departmentModel->getId()
                                )
                            )
                        )
                    );
                } else {
                    try {
                        $departmentModel->delete();
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    }
                }
            }
        }
        if (count($departmentIds) > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Total of %d record(s) were successfully deleted', count($departmentIds))
            );
        }
        $this->_redirect('*/*/index');
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('helpdeskultimate')->checkVersion('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }
}
