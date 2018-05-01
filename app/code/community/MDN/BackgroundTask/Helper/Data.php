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
class MDN_BackgroundTask_Helper_Data extends Mage_Core_Helper_Abstract {

    const RESULT_SUCCESS = 'success';
    const RESULT_ERROR = 'error';
    const RESULT_NOT_EXECUTED = 'null';
    /**
     * Add multiple task
     */
    public function AddMultipleTask($ids, $description, $helper, $method, $groupCode) {
        //build single query
        $singleQuery = "
							insert into " . Mage::getConfig()->getTablePrefix() . "backgroundtask
							(
								bt_created_at, 
								bt_description, 
								bt_helper, 
								bt_method, 
								bt_params, 
								bt_group_code
							)
							values
							(
								'" . date('Y-m-d H:i') . "',
								'" . $description . "',
								'" . $helper . "',
								'" . $method . "',
								'{value}',
								'" . $groupCode . "'
							);
						";


        //multiple query for all ids
        $allQueries = '';
        foreach ($ids as $id) {
            $value = serialize($id);
            $currentQuery = str_replace('{id}', $id, $singleQuery);
            $currentQuery = str_replace('{value}', $value, $currentQuery);
            $allQueries .= $currentQuery . "\n";
        }

        //run query
        mage::getResourceModel('cataloginventory/stock_item_collection')->getConnection()->query($allQueries);

        //update group task count
        if ($groupCode) {
            $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);
            if ($group == null)
                throw new Exception('Task group ' . $groupCode . ' doesnt exist');

            $group->updateTaskCount();
        }

        return true;
    }

    /**
     * Add a task to execute
     *
     * @param unknown_type $task
     */
    public function AddTask($description, $helper, $method, $params, $groupCode = null, $skipIfAlreadyPlanned = false, $priority = 1) {
        //if group is set, check  if group exists
        $group = null;
        if ($groupCode != null) {
            $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);
            if ($group == null)
                throw new Exception('Task group ' . $groupCode . ' doesnt exist for task '.$description);
        }
        else {
            //if task doesn't belong to group, check if is the same as last task
            if ($skipIfAlreadyPlanned) {
                if ($this->alreadyPlaned($helper, $method, $params))
                    return true;
            }
        }

        //define stack trace
        $stackTrace = '';
        if (mage::getStoreConfig('backgroundtask/general/store_stack_trace') == 1) {
            foreach (debug_backtrace () as $key => $value) {
                if (isset($value['file']) && isset($value['line']) && isset($value['function']))
                    $stackTrace .= $value['file'] . ' (' . $value['line'] . ') : ' . $value['function'] . "\n";
            }
        }

        //insert task
        $task = mage::getModel('BackgroundTask/task')
            ->setbt_created_at(date('Y-m-d h:i'))
            ->setbt_description($description)
            ->setbt_helper($helper)
            ->setbt_method($method)
            ->setbt_params(serialize($params))
            ->setbt_group_code($groupCode)
            ->setbt_priority($priority)
            ->setbt_stacktrace($stackTrace)
            ->save();

        //update group task count
        if ($group != null)
            $group->setbtg_task_count($group->getbtg_task_count() + 1)->save();

    }

    /**
     * Add a new task group
     *
     * @param unknown_type $groupCode
     * @param unknown_type $description
     * @param unknown_type $redirectUrl
     */
    public function AddGroup($groupCode, $description, $redirectUrl) {
        //if group exists, exit
        $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);
        if (!$group) {
            $group = mage::getModel('BackgroundTask/Taskgroup')
                ->setbtg_code($groupCode)
                ->setbtg_description($description)
                ->setbtg_redirect_url($redirectUrl)
                ->save();
        }
        return $group;
    }

    /**
     * Execute a task group
     * redirect to controller
     *
     * @param string $groupCode
     */
    public function ExecuteTaskGroup($groupCode) {
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/BackgroundTask_Admin/executeTaskGroup', array('group_code' => $groupCode));
        Mage::app()->getResponse()->setRedirect($url);
    }

    /**
     * Execute tasks (main module method)
     *
     */
    public function ExecuteTasks($refuseDebug = false, $methodName = null) {
        $debug = '<h1>Execute Tasks</h1>';
        $startTime = time();
        $hasTask = true;
        $maxExecutionTime = mage::getStoreConfig('backgroundtask/general/max_execution_time');
        while (((time() - $startTime) < $maxExecutionTime) && ($hasTask)) {
            //collect next task to execute
            $task = $this->getNextTaskToExecute($methodName);

            //execute task
            if ($task) {
                $debug .= '<br>Executing task #' . $task->getId() . ' (' . $task->getbt_description() . ')';
                $task->execute();
                $debug .= ' ---> ' . $task->getbt_status();
                if ($task->getbt_status() == 'error') {
                    $this->notifyDevelopper('Task #' . $task->getId() . ' failed.');
                }
            } else {
                //no task to execute, quit loop
                $hasTask = false;
            }
        }
        $debug .= '<br>End executing tasks';

        //delete tasks
        $debug .= '<br>Delete tasks';
        mage::getResourceModel('BackgroundTask/Task')->deleteTasks();

        //print debug information if enabled
        if ($refuseDebug == false) {
            if (mage::getStoreConfig('backgroundtask/general/debug') == 1)
                echo $debug;
        }

        return $debug;
    }

    /**
     * Collect the next task to execute
     *
     * @param string $methodName name of the method to execute
     */
    public function getNextTaskToExecute($methodName = null) {
        $collection = mage::getResourceModel('BackgroundTask/Task_Collection')->getNextTaskToExecute(null, $methodName);
        foreach ($collection as $item) {
            return $item;
        }
    }

    /**
     * Notify developer by email
     *
     * @param string $msg Content of the email
     */
    public function notifyDevelopper($msg) {
        $email = mage::getStoreConfig('backgroundtask/general/debug');
        if ($email != '') {
            mail($email, 'Magento Background Task notification', $msg);
        }
    }

    /**
     * Check if the last task is the same
     *
     * @param unknown_type $helper
     * @param unknown_type $method
     * @param unknown_type $params
     */
    protected function alreadyPlaned($helper, $method, $params) {
        $params = serialize($params);
        $collection = mage::getModel('BackgroundTask/Task')
            ->getCollection()
            ->addFieldToFilter('bt_helper', $helper)
            ->addFieldToFilter('bt_method', $method)
            ->addFieldToFilter('bt_params', $params)
            ->addFieldToFilter('bt_result', array('null' => 1));

        return ($collection->getSize() > 0);
    }

    /**
     * Force a task execution
     */
    public function forceTaskExecution($helper, $method, $params) {
        $params = serialize($params);

        $collection = mage::getModel('BackgroundTask/Task')
            ->getCollection()
            ->addFieldToFilter('bt_helper', $helper)
            ->addFieldToFilter('bt_method', $method)
            ->addFieldToFilter('bt_params', $params)
            ->addFieldToFilter('bt_result', array('null' => 1));
        foreach ($collection as $item) {
            $item->execute();
        }
    }

    /**
     * Get all backgroundtask results
     * @return array
     */
    public function getResults()
    {
        $t = array();
        $t[self::RESULT_ERROR] = Mage::helper('BackgroundTask')->__('Error');
        $t[self::RESULT_SUCCESS] = Mage::helper('BackgroundTask')->__('Success');
        $t[self::RESULT_NOT_EXECUTED] = Mage::helper('BackgroundTask')->__('Not executed');
        return $t;
    }

}
