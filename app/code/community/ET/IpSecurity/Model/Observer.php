<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Model_Observer
 */
class ET_IpSecurity_Model_Observer
{
    protected $_redirectPage = null;
    protected $_redirectBlank = null;
    protected $_rawAllowIpData = null;
    protected $_rawBlockIpData = null;
    protected $_rawExceptIpData = null;
    protected $_eventEmail = "";
    protected $_emailTemplate = 0;
    protected $_emailIdentity = null;
    protected $_storeType = null;
    protected $_lastFoundIp = null;
    protected $_isFrontend = false;
    protected $_isDownloader = false;
    protected $_alwaysNotify = false;

    /**
     * If loading Frontend
     *
     * Event: controller_action_predispatch
     * @param $observer
     */
    public function onLoadingFrontend($observer)
    {
        $this->_readFrontendConfig();
        $this->_processIpCheck($observer);
    }

    /**
     * If loading Admin
     *
     * Event: controller_action_predispatch
     * @param $observer
     */
    public function onLoadingAdmin($observer)
    {
        $this->_readAdminConfig();
        $this->_processIpCheck($observer);
    }

    /**
     * On failed login to Admin
     *
     * @param $observer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAdminLoginFailed($observer)
    {
        // TODO: for http://support.etwebsolutions.com/issues/371
    }

    /**
     * On loading Downloader
     *
     * Event: controller_front_init_routers
     * @param Varien_Event_Observer $observer
     */
    public function onLoadingDownloader($observer)
    {
        //only in downloader exists Maged_Controller class
        if (class_exists("Maged_Controller", false)) {
            $this->_readDownloaderConfig();
            $this->_processIpCheck($observer);
        }
    }

    /**
     * Reading configuration for Frontend
     */
    protected function _readFrontendConfig()
    {
        $this->_redirectPage = $this->trimTrailingSlashes(
            Mage::getStoreConfig('etipsecurity/ipsecurityfront/redirect_page'));
        $this->_redirectBlank = Mage::getStoreConfig('etipsecurity/ipsecurityfront/redirect_blank');
        $this->_rawAllowIpData = Mage::getStoreConfig('etipsecurity/ipsecurityfront/allow');
        $this->_rawBlockIpData = Mage::getStoreConfig('etipsecurity/ipsecurityfront/block');
        $this->_eventEmail = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_event');
        $this->_emailTemplate = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_template');
        $this->_emailIdentity = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_identity');
        $this->_alwaysNotify = Mage::getStoreConfig('etipsecurity/ipsecurityfront/email_always');
        $this->_rawExceptIpData = Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/except');

        $this->_storeType = Mage::helper("catalog")->__("Frontend");
        $this->_isFrontend = true;
    }

    /**
     * Reading configuration for Admin
     */
    protected function _readAdminConfig()
    {
        $this->_redirectPage = $this->trimTrailingSlashes(
            Mage::getStoreConfig('etipsecurity/ipsecurityadmin/redirect_page'));
        $this->_redirectBlank = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/redirect_blank');
        $this->_rawAllowIpData = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/allow');
        $this->_rawBlockIpData = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/block');
        $this->_eventEmail = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_event');
        $this->_emailTemplate = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_template');
        $this->_emailIdentity = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_identity');
        $this->_alwaysNotify = Mage::getStoreConfig('etipsecurity/ipsecurityadmin/email_always');

        $this->_storeType = Mage::helper("core")->__("Admin");
        $this->_isFrontend = false;
    }

    /**
     * Read configuration for Downloader (used Admin config)
     */
    protected function _readDownloaderConfig()
    {
        $this->_readAdminConfig();
        $this->_storeType = Mage::helper("etipsecurity")->__("Downloader");
        $this->_isDownloader = true;

        // TODO: заглушка. Если страницы для перехода не существует,
        // то поиск ссылки на no-rout вызывет ошибку.
        //$this->_redirectBlank = true;
    }

    /**
     * Get current Scope (frontend, admin, downloader)
     *
     * @return string
     */
    protected function _getScopeName()
    {
        if ($this->_isFrontend) {
            $scope = 'frontend';
        } elseif ($this->_isDownloader) {
            $scope = 'downloader';
        } else {
            $scope = 'admin';
        }

        return $scope;
    }

    /**
     * Checking current ip for rules
     *
     * @param Varien_Event_Observer $observer
     * @return ET_IpSecurity_Model_Observer
     */
    protected function _processIpCheck($observer)
    {
        $currentIp = $this->getCurrentIp();
        $allowIps = $this->_ipTextToArray($this->_rawAllowIpData);
        $blockIps = $this->_ipTextToArray($this->_rawBlockIpData);

        $allow = $this->isIpAllowed($currentIp, $allowIps, $blockIps);
        $this->_processAllowDeny($allow, $currentIp);

        return $this;
    }

    /**
     * Check IP for allow/deny rules
     *
     * @param $currentIp string
     * @param $allowIps array
     * @param $blockIps array
     * @return bool
     */
    public function isIpAllowed($currentIp, $allowIps, $blockIps)
    {
        $allow = true;

        # look for allowed
        if ($allowIps) {
            # block all except allowed
            $allow = false;

            # are there any allowed ips
            if ($this->isIpInList($currentIp, $allowIps)) {
                $allow = true;
            }
        }

        # look for blocked
        if ($blockIps) {
            # are there any blocked ips
            if ($this->isIpInList($currentIp, $blockIps)) {
                $allow = false;
            }
        }
        return $allow;
    }

    /**
     * Redirect denied users to block page or show maintenance page to visitor
     *
     * @param $allow boolean
     * @param $currentIp string
     */
    protected function _processAllowDeny($allow, $currentIp)
    {
        //TODO: Refactoring?
        $currentPage = $this->trimTrailingSlashes(Mage::helper('core/url')->getCurrentUrl());
        // searching for CMS page storeId
        // if we don't do it - we have loop in redirect with setting Add Store Code to Urls = Yes
        // (block access to admin redirects to admin)
        $pageStoreId = $this->getPageStoreId();
        $this->_redirectPage = $this->trimTrailingSlashes(Mage::app()->getStore($pageStoreId)->getBaseUrl())
            . "/" . $this->_redirectPage;
        $scope = $this->_getScopeName();

        if (!strlen($this->_redirectPage) && !$this->_isDownloader) {
            $this->_redirectPage = $this->trimTrailingSlashes(Mage::getUrl('no-route'));
        }

        if ($this->_redirectBlank == 1 && !$allow) {
            header("HTTP/1.1 403 Forbidden");
            header("Status: 403 Forbidden");
            header("Content-type: text/html");
            $needToNotify = $this->saveToLog(array('blocked_from' => $scope, 'blocked_ip' => $currentIp));
            if (($this->_alwaysNotify) || $needToNotify) {
                $this->_send();
            }
            exit("Access denied for IP:<b> " . $currentIp . "</b>");
        }

        if ($this->trimTrailingSlashes($currentPage) != $this->trimTrailingSlashes($this->_redirectPage) && !$allow) {
            header('Location: ' . $this->_redirectPage);
            $needToNotify = $this->saveToLog(array('blocked_from' => $scope, 'blocked_ip' => $currentIp));
            if (($this->_alwaysNotify) || $needToNotify) {
                $this->_send();
            }
            exit();
        }

        $exceptIps = $this->_ipTextToArray($this->_rawExceptIpData);
        $isMaintenanceMode = Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/enabled');
        if (($isMaintenanceMode) && ($this->_isFrontend)) {
            $doNotLoadSite = true;
            # look for except
            if ($exceptIps) {
                # are there any except ips
                if ($this->isIpInList($currentIp, $exceptIps)) {
                    Mage::app()->getResponse()->appendBody(
                        html_entity_decode(
                            Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/remindermessage'),
                            ENT_QUOTES,
                            "utf-8"
                        )
                    );
                    $doNotLoadSite = false;
                }
            }

            if ($doNotLoadSite) {
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 7200'); // in seconds
                print html_entity_decode(
                    Mage::getStoreConfig('etipsecurity/ipsecuritymaintetance/message'),
                    ENT_QUOTES,
                    "utf-8"
                );
                exit();
            }

        }
    }


    /**
     * Get store id of target redirect cms page
     *
     * @return int
     */
    public function getPageStoreId()
    {
        $stores = array();
        $pageStoreIds = array();

        foreach (Mage::app()->getStores() as $store) {
            /* @var $store Mage_Core_Model_Store */
            $stores[] = $store->getId();
            $pageId = Mage::getModel('cms/page')->checkIdentifier($this->_redirectPage, $store->getId());
            if ($pageId === false) {
                continue;
            }
            $pageStoreIds = Mage::getResourceModel('cms/page')->lookupStoreIds($pageId);
            if (count($pageStoreIds)) { // found page
                break;
            }
        }

        if (!count($pageStoreIds)) { // no found in any store
            $pageStoreIds[] = 0;
        }
        //default
        $pageStoreId = 0;
        foreach ($pageStoreIds as $pageStoreId) {
            if ($pageStoreId > 0) {
                break;
            }
        }

        if ($pageStoreId == 0) {
            $pageStoreId = $stores[0];
            return $pageStoreId; // first available store
        }
        return $pageStoreId;
    }


    /**
     * Convert IP range as string to array with first and last IP of range
     *
     * @param $ipRange string
     * @return array[first,last]
     */
    protected function _convertIpStringToIpRange($ipRange)
    {
        $ip = explode("|", $ipRange);
        $ip = trim($ip[0]);
        $simpleRange = explode("-", $ip);
        //for xx.xx.xx.xx-yy.yy.yy.yy
        if (count($simpleRange) == 2) {
            $comparableIpRange = array(
                "first" => $this->_convertIpToComparableString($simpleRange[0]),
                "last" => $this->_convertIpToComparableString($simpleRange[1]));
            return $comparableIpRange;
        }
        //for xx.xx.xx.*
        if (strpos($ip, "*") !== false) {
            $fromIp = str_replace("*", "0", $ip);
            $toIp = str_replace("*", "255", $ip);
            $comparableIpRange = array(
                "first" => $this->_convertIpToComparableString($fromIp),
                "last" => $this->_convertIpToComparableString($toIp));
            return $comparableIpRange;
        }
        //for xx.xx.xx.xx/yy
        $maskRange = explode("/", $ip);
        if (count($maskRange) == 2) {
            $maskMoves = 32 - $maskRange[1];
            $mask = (0xFFFFFFFF >> $maskMoves) << $maskMoves;
            $subMask = 0;
            for ($maskDigits = 0; $maskDigits < $maskMoves; $maskDigits++) {
                $subMask = ($subMask << 1) | 1;
            }
            $fromIp = ip2long($maskRange[0]) & $mask;
            $toIp = long2ip($fromIp | $subMask);
            $fromIp = long2ip($fromIp);
            $comparableIpRange = array(
                "first" => $this->_convertIpToComparableString($fromIp),
                "last" => $this->_convertIpToComparableString($toIp));
            return $comparableIpRange;
        }

        $comparableIpRange = array(
            "first" => $this->_convertIpToComparableString($ip),
            "last" => $this->_convertIpToComparableString($ip)
        );

        return $comparableIpRange;

    }

    /**
     * Convert IP address (x.xx.xxx.xx) to easy comparable string (xxx.xxx.xxx.xxx)
     *
     * @param $ip string
     * @return string
     * @throws Exception
     */
    protected function _convertIpToComparableString($ip)
    {
        $partsOfIp = explode(".", trim($ip));
        if (count($partsOfIp) != 4) {
            throw new Exception("Incorrect IP format: " . $ip);
        }
        $comparableIpString = sprintf(
            "%03d%03d%03d%03d",
            $partsOfIp[0],
            $partsOfIp[1],
            $partsOfIp[2],
            $partsOfIp[3]
        );
        return $comparableIpString;

    }

    /**
     * Is ip in list of IP rules
     *
     * @param $searchIp string
     * @param $ipRulesList array
     * @return bool
     */
    public function isIpInList($searchIp, $ipRulesList)
    {
        $searchIpComparable = $this->_convertIpToComparableString($searchIp);
        if (count($ipRulesList) > 0) {
            foreach ($ipRulesList as $ipRule) {
                $ip = explode("|", $ipRule);
                $ip = trim($ip[0]);
                try {
                    $ipRange = $this->_convertIpStringToIpRange($ip);
                    //var_dump($ipRange);
                    if (count($ipRange) == 2) {
                        $ipFrom = $ipRange["first"];
                        $ipTo = $ipRange["last"];
                        if ((strcmp($ipFrom, $searchIpComparable) <= 0) &&
                            (strcmp($searchIpComparable, $ipTo) <= 0)
                        ) {
                            $this->_lastFoundIp = $ipRule;
                            return true;
                        }
                    }
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }
                //}
            }
        }
        return false;
    }

    /**
     * Trim trailing slashes, except single "/"
     *
     * @param $str string
     * @return string
     */
    protected function trimTrailingSlashes($str)
    {
        $str = trim($str);
        return $str == '/' ? $str : rtrim($str, '/');
    }

    /**
     * Send to admin information about IP blocking
     */
    protected function _send()
    {
        $sendResult = false;
        if (!$this->_eventEmail) {
            return $sendResult;
        }
        $currentIp = $this->getCurrentIp();
        //$storeId = 0; //admin

        $recipients = explode(",", $this->_eventEmail);

        /* @var Mage_Core_Model_Email_Template $emailTemplate */
        $emailTemplate = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'backend'));
        $coreHelper = Mage::helper('core');
        $coreUrlHelper = Mage::helper('core/url');
        foreach ($recipients as $recipient) {
            $sendResult = $emailTemplate
                ->sendTransactional(
                    $this->_emailTemplate,
                    $this->_emailIdentity,
                    trim($recipient),
                    trim($recipient),
                    array(
                        'ip' => $currentIp,
                        'ip_rule' => Mage::helper('etipsecurity')->__($this->getLastBlockRule()),
                        'date' => $coreHelper->formatDate(null, Mage_Core_Model_Locale::FORMAT_TYPE_FULL, true),
                        'storetype' => $this->_storeType,
                        'url' => $coreUrlHelper->getCurrentUrl(),
                        'info' => base64_encode(serialize(array($this->_rawAllowIpData, $this->_rawBlockIpData))),
                    )
                );
        }
        return $sendResult;
    }

    /**
     * Return block rule
     *
     * @return string
     */
    public function getLastBlockRule()
    {
        $lastBlockRule = 'Not in allowed list';
        if (!is_null($this->_lastFoundIp)) {
            $lastBlockRule = $this->_lastFoundIp;
        }
        return $lastBlockRule;
    }

    /**
     * Get IP of current client
     *
     * @return string
     */
    public function getCurrentIp()
    {
        $helper = Mage::helper('etipsecurity');
        $selectedIpVariable = $helper->getIpVariable();
        $currentIp = $_SERVER[$selectedIpVariable];
        return $this->_getCurrentIp($currentIp, $selectedIpVariable);
    }

    /**
     * HTTP_X_FORWARDED_FOR can return comma delimetered list of IP addresses.
     * We need only one IP address to check
     *
     * @param $currentIp
     * @param $selectedIpVariable
     * @return string
     */
    protected function _getCurrentIp($currentIp, $selectedIpVariable)
    {
        switch ($selectedIpVariable) {
            case 'HTTP_X_FORWARDED_FOR':
                $resultArray = explode(',', $currentIp);
                $result = trim($resultArray[0]);
                break;
            default:
                $result = $currentIp;
        }
        return $result;
    }

    /**
     * Convert string with IP to IP array
     *
     * @param $text string
     * @return array
     */
    protected function _ipTextToArray($text)
    {
        $ips = preg_split("/[\n\r]+/", $text);
        foreach ($ips as $ipsk => $ipsv) {
            if (trim($ipsv) == "") {
                unset($ips[$ipsk]);
            }
        }
        return $ips;
    }

    /**
     * Save Blocked IP to log
     *
     * @param array $params
     * @return bool
     */
    protected function saveToLog($params = array())
    {
        $needNotify = true;

        if (!((isset($params['blocked_ip'])) && (strlen(trim($params['blocked_ip'])) > 0))) {
            $params['blocked_ip'] = $this->getCurrentIp();
        }

        if (!((isset($params['blocked_from'])) && (strlen(trim($params['blocked_from'])) > 0))) {
            $params['blocked_from'] = 'undefined';
        }

        $now = now();

        /* @var $logTable ET_IpSecurity_Model_Mysql4_Ipsecuritylog_Collection */
        $logTable = Mage::getModel('etipsecurity/ipsecuritylog')->getCollection();
        $logTable->getSelect()->where('blocked_from=?', $params['blocked_from'])
            ->where('blocked_ip=?', $params['blocked_ip']);

        if (count($logTable) > 0) {
            foreach ($logTable as $row) {
                /* @var $row ET_IpSecurity_Model_Ipsecuritylog */
                $timesBlocked = $row->getData('qty') + 1;
                $row->setData('qty', $timesBlocked);
                $row->setData('last_block_rule', $this->getLastBlockRule());
                $row->setData('update_time', $now);
                $row->save();
                if (($timesBlocked % 10) == 0) {
                    $needNotify = true;
                } else {
                    $needNotify = false;
                }
            }
        } else {
            /** @var ET_IpSecurity_Model_Ipsecuritylog $log */
            $log = Mage::getModel('etipsecurity/ipsecuritylog');

            $log->setData('blocked_from', $params['blocked_from']);
            $log->setData('blocked_ip', $params['blocked_ip']);
            $log->setData('qty', '1');
            $log->setData('last_block_rule', $this->getLastBlockRule());
            $log->setData('create_time', $now);
            $log->setData('update_time', $now);

            $log->save();
            $needNotify = true;
        }

        // if returns true - IP blocked for first time or timesBloked is 10, 20, 30 etc.
        return $needNotify;
    }

}