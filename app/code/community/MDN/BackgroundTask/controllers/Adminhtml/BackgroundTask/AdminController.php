<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
//Controlleur pour la gestion des contacts
class MDN_BackgroundTask_Adminhtml_BackgroundTask_AdminController extends Mage_Adminhtml_Controller_Action {

    /**
     * Tasks grid
     *
     */
    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Background Tasks'));

        $this->renderLayout();
    }

    /**
     * View task
     *
     */
    public function ViewAction() {
        $this->loadLayout();

        $id = $this->getRequest()->getParam('bt_id');

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Background Task #'.$id));

        $this->renderLayout();
    }

    /**
     * Stats
     *
     */
    public function StatsAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Stats'));

        $this->renderLayout();
    }

    /**
     * Display task group progress page
     *
     */
    public function executeTaskGroupAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Execute tasks group
     *
     */
    public function AjaxExecuteTaskGroupAction() {
        $groupCode = $this->getRequest()->getParam('group_code');
        $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);
        $hasError = 0;
        $errorMessage = '';
        $hasFinished = 0;
        $progressPercent = 0;

        try {
            $errorMessage = $group->execute();
        } catch (Exception $ex) {
            $hasError = 1;
            $errorMessage = $ex->getMessage();
        }

        //set values
        $progressPercent = $group->getProgressPercent();

        if ((int) $progressPercent >= 100)
            $hasFinished = 1;

        if ($errorMessage != '')
            $hasError = 1;

        //return result
        $response = array();
        $response['error'] = $hasError;
        $response['error_message'] = $errorMessage;
        $response['finished'] = $hasFinished;
        $response['progress'] = $progressPercent;
        $response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
    }

    /**
     * Confirme group task execution and redirect to specify url
     *
     */
    public function confirmTaskGroupExecutionAction() {
        //retrieve information
        $groupCode = $this->getRequest()->getParam('group_code');
        $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);

        //delete group and tasks
        $group->delete();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tasks successfully performed'));

        //redirect
        $url = $group->getbtg_redirect_url();
        $this->_redirect($url);
    }

    /**
     * Clear all tasks
     *
     */
    public function ClearAllTasksAction() {
        mage::getResourceModel('BackgroundTask/Task')->deleteAllTasks();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tasks successfully deleted'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/Grid');
    }

    /**
     * Clear all tasks
     *
     */
    public function ClearGroupTasksAction() {
        mage::getResourceModel('BackgroundTask/Task')->deleteAllGroupTasks();
        mage::getResourceModel('BackgroundTask/Taskgroup')->deleteAllGroups();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Group Tasks successfully deleted'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/Grid');
    }

    /**
     * Clean Success tasks
     *
     */
    public function ClearSuccessTasksAction() {
        mage::getResourceModel('BackgroundTask/Task')->deleteSuccessTasks();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tasks successfully deleted'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/Grid');
    }

    /**
     * Run back ground tasks
     *
     */
    public function RunNextTasksAction() {
        mage::helper('BackgroundTask')->ExecuteTasks();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tasks executed'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/Grid');
    }

    /**
     * Replay task
     *
     */
    public function ReplayAction() {

        $taskId = $this->getRequest()->getParam('bt_id');
        $task = mage::getModel('BackgroundTask/Task')->load($taskId);
        $task->execute();

        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Task executed'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/View', array('bt_id' => $taskId));
    }

    public function MassReplayAction() {
        $taskIds = $this->getRequest()->getParam('bt_ids');
        foreach($taskIds as $taskId)
        {
            $task = mage::getModel('BackgroundTask/Task')->load($taskId);
            $task->execute();
        }
        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tasks executed'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/Grid');
    }

    public function MassLowPriorityAction() {
        $taskIds = $this->getRequest()->getParam('bt_ids');
        foreach($taskIds as $taskId)
        {
            $task = mage::getModel('BackgroundTask/Task')->load($taskId);
            $task->setbt_priority($task->getbt_priority() + 5);
            $task->save();
        }
        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tasks updated'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/Grid');
    }



    public function MassDeleteAction() {
        $taskIds = $this->getRequest()->getParam('bt_ids');
        foreach($taskIds as $taskId)
        {
            $task = mage::getModel('BackgroundTask/Task')->load($taskId);
            $task->delete();
        }
        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tasks deleted'));
        $this->_redirect('adminhtml/BackgroundTask_Admin/Grid');
    }


    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/tools/background_task');
    }


}