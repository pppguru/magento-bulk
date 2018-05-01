<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Cron
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Crontab observer
 *
 * @category    Mage
 * @package     Mage_Cron
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_BackgroundTask_Model_CronObserver extends Mage_Cron_Model_Observer
{
    const DEBUG = true;
    const DEBUG_LOG_FILE = 'cron_execution.log';

    public function cronLog($message){
        if(self::DEBUG){
            echo '<br/>'.$message;
        }else{
            Mage::log($message, null, self::DEBUG_LOG_FILE);
        }
    }

    /**
     * Process cron queue
     * Generate tasks schedule
     * Cleanup tasks schedule
     *
     * @param Varien_Event_Observer $observer
     */
    public function dispatch($observer)
    {
        $begin = microtime(true);
        $this->cronLog('Begin Cron execution at '.date('Y-m-d H:i:s')."\n");

        $schedules = $this->getPendingSchedules();
        $this->cronLog('<b>NB task to execute :'.count($schedules).'</b><br/>');

        $jobsRoot = Mage::getConfig()->getNode('crontab/jobs');
        $defaultJobsRoot = Mage::getConfig()->getNode('default/crontab/jobs');

        /** @var $schedule Mage_Cron_Model_Schedule */
        foreach ($schedules->getIterator() as $schedule) {
            $jobConfig = $jobsRoot->{$schedule->getJobCode()};
            $this->cronLog('LOAD task #'.$schedule->getschedule_id().' : <b>'.$schedule->getjob_code().'</b> scheduled at '.$schedule->getScheduledAt());

            if (!$jobConfig || !$jobConfig->run) {
                $jobConfig = $defaultJobsRoot->{$schedule->getJobCode()};
                if (!$jobConfig || !$jobConfig->run) {
                    continue;
                }
            }
            $this->_processJob($schedule, $jobConfig);
        }

        $this->generate();
        $this->cleanup();

        $end = microtime(true);
        $duration = ($end - $begin);
        $this->cronLog('<b>End Cron execution at '.date('Y-m-d H:i:s').' in '.$duration.'s </b><br/>');
    }



    /**
     * Generate cron schedule
     *
     * @return Mage_Cron_Model_Observer
     */
    public function generate()
    {
        $this->cronLog('<br>Begin GENERATING NEXT TASKS at '.date('Y-m-d H:i:s'));
        /**
         * check if schedule generation is needed
         */
        $lastRun = Mage::app()->loadCache(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT);
        if ($lastRun > time() - Mage::getStoreConfig(self::XML_PATH_SCHEDULE_GENERATE_EVERY)*60) {
            $this->cronLog('SKIP GENERATING NEXT TASKS at '.date('Y-m-d H:i:s').'<br/>');
            return $this;
        }

        $schedules = $this->getPendingSchedules();
        $exists = array();
        foreach ($schedules->getIterator() as $schedule) {
            $exists[$schedule->getJobCode().'/'.$schedule->getScheduledAt()] = 1;
        }

        /**
         * generate global crontab jobs
         */
        $config = Mage::getConfig()->getNode('crontab/jobs');
        if ($config instanceof Mage_Core_Model_Config_Element) {
            $this->_generateJobs($config->children(), $exists);
        }

        /**
         * generate configurable crontab jobs
         */
        $config = Mage::getConfig()->getNode('default/crontab/jobs');
        if ($config instanceof Mage_Core_Model_Config_Element) {
            $this->_generateJobs($config->children(), $exists);
        }

        /**
         * save time schedules generation was ran with no expiration
         */
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT, array('crontab'), null);
        $this->cronLog('End GENERATING NEXT TASKS at '.date('Y-m-d H:i:s').'<br/>');

        return $this;
    }



    /**
     * Clean up the history of tasks
     *
     * @return Mage_Cron_Model_Observer
     */
    public function cleanup()
    {
        $this->cronLog('<br>Begin CLEANING TASKS  at '.date('Y-m-d H:i:s'));
        // check if history cleanup is needed
        $lastCleanup = Mage::app()->loadCache(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT);
        if ($lastCleanup > time() - Mage::getStoreConfig(self::XML_PATH_HISTORY_CLEANUP_EVERY)*60) {
            $this->cronLog('SKIP CLEANING TASKS  at '.date('Y-m-d H:i:s').'<br/>');
            return $this;
        }

        $history = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('status', array('in'=>array(
                Mage_Cron_Model_Schedule::STATUS_SUCCESS,
                Mage_Cron_Model_Schedule::STATUS_MISSED,
                Mage_Cron_Model_Schedule::STATUS_ERROR,
            )))
            ->load();

        $historyLifetimes = array(
            Mage_Cron_Model_Schedule::STATUS_SUCCESS => Mage::getStoreConfig(self::XML_PATH_HISTORY_SUCCESS)*60,
            Mage_Cron_Model_Schedule::STATUS_MISSED => Mage::getStoreConfig(self::XML_PATH_HISTORY_FAILURE)*60,
            Mage_Cron_Model_Schedule::STATUS_ERROR => Mage::getStoreConfig(self::XML_PATH_HISTORY_FAILURE)*60,
        );

        $now = time();
        foreach ($history->getIterator() as $record) {
            if (strtotime($record->getExecutedAt()) < $now-$historyLifetimes[$record->getStatus()]) {
                $record->delete();
            }
        }

        // save time history cleanup was ran with no expiration
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT, array('crontab'), null);
        $this->cronLog('End CLEANING TASKS  at '.date('Y-m-d H:i:s').'<br/>');

        return $this;
    }



    /**
     * Process cron task
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @param $jobConfig
     * @param bool $isAlways
     * @return Mage_Cron_Model_Observer
     */
    protected function _processJob($schedule, $jobConfig, $isAlways = false)
    {
        // $this->cronLog('BEGIN processJob task #'.$schedule->getschedule_id().' : <b>'.$schedule->getjob_code().'</b> at '.date('Y-m-d H:i:s'));
        $runConfig = $jobConfig->run;
        if (!$isAlways) {
            $scheduleLifetime = Mage::getStoreConfig(self::XML_PATH_SCHEDULE_LIFETIME) * 60;
            $now = time();
            $time = strtotime($schedule->getScheduledAt());
            if ($time > $now) {
                $this->cronLog(' -> SKIP task #'.$schedule->getschedule_id().' : <b>'.$schedule->getjob_code().'</b><br/>');
                return;
            }
        }

        $errorStatus = Mage_Cron_Model_Schedule::STATUS_ERROR;
        try {
            if (!$isAlways) {
                if ($time < $now - $scheduleLifetime) {
                    $errorStatus = Mage_Cron_Model_Schedule::STATUS_MISSED;
                    $this->cronLog(Mage::helper('cron')->__('Too late for the schedule.'));
                    Mage::throwException(Mage::helper('cron')->__('Too late for the schedule.'));
                }
            }
            if ($runConfig->model) {
                if (!preg_match(self::REGEX_RUN_MODEL, (string)$runConfig->model, $run)) {
                    $this->cronLog(Mage::helper('cron')->__('Invalid model/method definition, expecting "model/class::method".'));
                    Mage::throwException(Mage::helper('cron')->__('Invalid model/method definition, expecting "model/class::method".'));
                }
                if (!($model = Mage::getModel($run[1])) || !method_exists($model, $run[2])) {
                    $this->cronLog(Mage::helper('cron')->__('Invalid callback: %s::%s does not exist', $run[1], $run[2]));
                    Mage::throwException(Mage::helper('cron')->__('Invalid callback: %s::%s does not exist', $run[1], $run[2]));
                }
                $callback = array($model, $run[2]);
                $arguments = array($schedule);
            }
            if (empty($callback)) {
                $this->cronLog(Mage::helper('cron')->__('No callbacks found'));
                Mage::throwException(Mage::helper('cron')->__('No callbacks found'));
            }

            if (!$isAlways) {
                if (!$schedule->tryLockJob()) {
                    $this->cronLog('SKIP task #'.$schedule->getschedule_id().' : <b>'.$schedule->getjob_code().'</b> at '.date('Y-m-d H:i:s').'<br/>');
                    // another cron started this job intermittently, so skip it
                    return;
                }
                /**
                though running status is set in tryLockJob we must set it here because the object
                was loaded with a pending status and will set it back to pending if we don't set it here
                 */
            }

            $begin = microtime(true);
            $this->cronLog('EXEC task #'.$schedule->getschedule_id().' : <b>'.$schedule->getjob_code().'</b> at '.date('Y-m-d H:i:s'));

            $schedule
                ->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                ->save();

            call_user_func_array($callback, $arguments);

            $schedule
                ->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS)
                ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));

            $end = microtime(true);
            $duration = ($end - $begin);
            $this->cronLog('<b>END task #'.$schedule->getschedule_id().' : '.$schedule->getjob_code().' in '.$duration.'s </b><br/>');

        } catch (Exception $e) {
            $schedule->setStatus($errorStatus)
                ->setMessages($e->__toString());
        }
        $schedule->save();

        return $this;
    }


}
