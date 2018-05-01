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

class AW_Lib_Helper_Log
{
    const SEVERITY_NOTICE = "notice";
    const SEVERITY_WARNING = "warning";
    const SEVERITY_ERROR = "error";
    const SEVERITY_STRICT = "strict";

    const XML_PATH_ENABLE_LOG = 'awall/aw_lib/logger_enabled';

    /**
     * array for logs
     *
     * @var array
     */
    protected static $_logs = array();
    protected static $_fileInfo = array();

    protected static $_severitiesPriority = array(
        'notice'  => 1,
        'warning' => 2,
        'error'   => 3,
        'strict'  => 4
    );

    protected static $_level = 0;
    protected static $_maxSeverity = 'notice';
    protected static $_currentSeverity = 'notice';

    public static function start()
    {
        $backtrace = debug_backtrace();
        $args = func_get_args();
        if ($args) {
            self::$_currentSeverity = self::SEVERITY_NOTICE;
            self::_addLog($args);
            self::_addFileInfo($backtrace);
        }
        self::$_level++;
    }

    public static function log($args, $severity = null)
    {
        $backtrace = debug_backtrace();

        self::_compareSeverities($severity);
        self::_addLog($args);
        self::_addFileInfo($backtrace);

        if (self::$_level == 0) {
            $title = strip_tags(self::$_logs[0]);
            $className = $backtrace[1]["class"];
            $_parts = explode('_', $className);
            $moduleName = $_parts[1];
            self::_release($title, $moduleName);
        }
    }

    public static function stop()
    {
        $backtrace = debug_backtrace();
        $args = func_get_args();
        self::$_level--;
        if (self::$_level < 0) {
            self::$_level = 0;
        }
        if ($args) {
            self::$_currentSeverity = self::SEVERITY_NOTICE;
            self::_addLog($args);
            self::_addFileInfo($backtrace);
        }
        if (self::$_level == 0) {
            $title = strip_tags(self::$_logs[0]);
            $className = $backtrace[1]["class"];
            $_parts = explode('_', $className);
            $moduleName = $_parts[1];
            self::_release($title, $moduleName);
        }
    }


    protected static function _clear()
    {
        self::$_fileInfo = array();
        self::$_logs = array();
        self::$_maxSeverity = self::SEVERITY_NOTICE;
        self::$_currentSeverity = self::SEVERITY_NOTICE;
        self::$_level = 0;
    }

    protected static function _release($title, $moduleName)
    {
        if (!Mage::getStoreConfig(self::XML_PATH_ENABLE_LOG)) {
            self::_clear();
            return;
        }

        $content = '';
        foreach (self::$_logs as $_logRow) {
            $content .= $_logRow . "\n";
        }

        $fileInfo = '';
        foreach (self::$_fileInfo as $_fileInfoRow) {
            $fileInfo .= $_fileInfoRow . "\n";
        }

        $logger = Mage::getModel("aw_lib/log_logger");
        $logger
            ->setTitle($title)
            ->setSeverity(self::$_maxSeverity)
            ->setContent($content)
            ->setModule($moduleName)
            ->setFileInfo($fileInfo)
            ->save();

        self::_clear();
    }

    protected static function _addLog($args)
    {
        $msg = $args;
        if (is_array($args)) {
            $msg = call_user_func_array('sprintf', array_values($args));
        }

        self::$_logs[] = '<span class="aw-lib-row-severity-' . self::$_currentSeverity . '">'
                         . self::_getMessageWithTabs($msg) . '</span>'
        ;
    }

    protected static function _addFileInfo($backtrace)
    {
        $fileInfo = '&nbsp;';
        if ($backtrace) {
            $_className = $backtrace[1]["class"];
            $_methodName = $backtrace[1]["function"];
            $_codeLine = $backtrace[0]["line"];
            $fileInfo = $_className . "::" . $_methodName . ":" . $_codeLine;
        }

        self::$_fileInfo[] = '<span class="aw-lib-row-severity-' . self::$_currentSeverity . '">'
                             . $fileInfo . '</span>'
        ;
    }

    protected static function _getMessageWithTabs($msg)
    {
        $_tabCount = self::$_level;
        $_tabString = "";
        while ($_tabCount > 0) {
            $_tabString .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $_tabCount--;
        }
        return $_tabString . $msg;

    }

    protected static function _compareSeverities($severity = null)
    {
        if (!$severity) {
            $severity = self::SEVERITY_NOTICE;
        }
        self::$_currentSeverity = $severity;
        if (self::$_severitiesPriority[self::$_maxSeverity] < self::$_severitiesPriority[$severity]) {
            self::$_maxSeverity = $severity;
        }
    }
}