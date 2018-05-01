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

/**
 * Collection de quotation
 *
 */
class MDN_BackgroundTask_Model_Mysql4_Task_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    private static $taskStack = array();

    const kStackSize = 1000;

    public function _construct() {
        parent::_construct();
        $this->_init('BackgroundTask/Task');
    }

    /**
     * Return next task to execute
     * Exclude grouped task
     *
     */
    public function getNextTaskToExecute($groupCode = null, $methodName = null) {
        //if group does not exist, load it
        if (!isset(self::$taskStack[$groupCode])) {

            $this->getSelect()->where('bt_executed_at is NULL');

            if ($groupCode == null)
                $this->getSelect()->where('bt_group_code is NULL');
            else
                $this->getSelect()->where("bt_group_code = '" . $groupCode . "'");

            if ($methodName !== null)
                $this->getSelect()->where("bt_method = '" . (string) $methodName . "'");

            $this->getSelect()->order('bt_priority asc');
            $this->getSelect()->order('bt_id asc');
            $this->getSelect()->limit(self::kStackSize);

            $collection = $this->load();

            self::$taskStack[$groupCode] = array();
            foreach ($collection as $item) {
                self::$taskStack[$groupCode][] = $item;
            }
        }

        $item = array_shift(self::$taskStack[$groupCode]);
        if (count(self::$taskStack[$groupCode]) == 0)
            unset(self::$taskStack[$groupCode]);

        return array($item);
    }



}
