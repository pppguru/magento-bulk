<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Product Feeds
 * @version   1.1.2
 * @build     428
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_MstCore_Helper_Code extends Mage_Core_Helper_Data
{
    const LICENSE_URL = 'http://mirasvit.com/lc/check/';

    const STATUS_APPROVED = 'APPROVED';

    const EE_EDITION = 'EE';
    const PE_EDITION = 'PE';
    const CE_EDITION = 'CE';

    const STATUS_KEY = 'mstcore_status';

    protected static $_edition = false;

    protected $o;
    protected $k;
    protected $s;
    protected $r;
    protected $v;
    protected $p;

    public function getCodeHelper($moduleName)
    {
        $file = Mage::getBaseDir().'/app/code/local/Mirasvit/'.$moduleName.'/Helper/Code.php';
        
        if (file_exists($file)) {
            $helper = Mage::helper(strtolower($moduleName).'/code');
            return $helper;
        }

        return false;
    }
 
    public function getStatus($class = null)
    {
        $this->refresh();

        $module = $this->_getModuleNameByBacktrace($class);

        if ($module) {
            $codeHelper = $this->getCodeHelper($module);
            if ($codeHelper) {
                $sku = $codeHelper->getSku();

                $status = $this->_getStatus();

                if (is_array($status) && isset($status[$sku])) {
                    if ($status[$sku]['status'] == 'BANNED') {
                        return $status[$sku]['message'];
                    }
                }
            }
        }

        return true;
    }

    /**
     * Return module name (SearchIndex, AsyncIndex, Fpc) by backtrace or class object
     */
    protected function _getModuleNameByBacktrace($class = null)
    {
        if ($class != null && is_object($class)) {
            $class =  explode('_', get_class($class));
            if (isset($class[1])) {
                return $class[1];
            }
        } else {
            $backtrace = debug_backtrace();

            if (is_array($backtrace) && isset($backtrace[2]) && isset($backtrace[2]['class'])) {
                $class =  explode('_', $backtrace[2]['class']);
                if (isset($class[1])) {
                    return $class[1];
                }
            }
        }

        return false;
    }

    public function onModelSaveBefore($observer)
    {
        $obj = $observer->getObject();

        if (is_object($obj) && substr(get_class($obj), 0, 9) == 'Mirasvit_') {
            $status = $this->getStatus($obj);
            if ($status !== true) {
                die($status);
            }
        }
    }

    /** INTERNAL METHODS */

    private function refresh($force = false)
    {
        if ($force || time() - Mage::app()->loadCache(md5(self::LICENSE_URL)) > 24 * 60 * 60) {
            $params       = array();
            $params['v']  = 2;
            $params['d']  = Mage::getBaseUrl();
            $params['ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
            $params['mv'] = Mage::getVersion();
            $params['me'] = self::getEdition();
            $extensions   = $this->getOurExtensions();
            $keys         = array();

            $params['p'] = serialize($extensions);

            try {
                Mage::app()->saveCache(time(), md5(self::LICENSE_URL));

                $result = $this->_getResponse(self::LICENSE_URL, $params);

                if (!$result || $result == '') {
                    return $this;
                }

                $result   = base64_decode($result);
                $xml      = simplexml_load_string($result);
                $products = array();

                try {
                    if ($record = Mage::getStoreConfig('mstcore/system/status')) {
                       $products = str_rot13(base64_decode(@unserialize($record)));
                    }
                } catch (Exception $ex) {}

                foreach ($xml->products->product as $product) {
                    $products[(string)$product->sku] = array(
                        'status'  => (string) $product->status,
                        'message' => (string) $product->message
                    );
                }

                $this->_saveStatus($products);
            } catch (Exception $ex) {}

            return $this;
        }
    }

    private static function getEdition()
    {
        if (!self::$_edition) {
            $pathToClaim    = BP.DS."app".DS."etc".DS."modules".DS.'Enterprise'."_".'Enterprise'.".xml";
            $pathToEEConfig = BP.DS."app".DS."code".DS."core".DS.'Enterprise'.DS.'Enterprise'.DS."etc".DS."config.xml";
            $isCommunity    = !file_exists($pathToClaim) || !file_exists($pathToEEConfig);

            if ($isCommunity) {
                 self::$_edition = self::CE_EDITION;
            } else {
                $_xml = @simplexml_load_file($pathToEEConfig, 'SimpleXMLElement', LIBXML_NOCDATA);
                if(!$_xml === FALSE) {
                    $package = (string)$_xml->default->design->package->name;
                    $theme   = (string)$_xml->install->design->theme->default;
                    $skin    = (string)$_xml->stores->admin->design->theme->skin;

                    $isProffessional = ($package == "pro") && ($theme == "pro") && ($skin == "pro");

                    if ($isProffessional) {
                        self::$_edition = self::PE_EDITION;
                        return self::$_edition;
                    }
                }

                self::$_edition = self::EE_EDITION;
            }
        }

        return self::$_edition;
    }

    public function getOurExtensions()
    {
        $extensions = array();

        foreach (Mage::getConfig()->getNode('modules')->children() as $name => $module) {
            if ($module->active != 'true') {
                continue;
            }
            if (strpos($name, 'Mirasvit_') === 0) {
                if ($name == 'Mirasvit_MstCore' || $name == 'Mirasvit_MCore') {
                    continue;
                }

                $parts = explode('_', $name);

                if ($helper = $this->getCodeHelper($parts[1])) {
                    if (method_exists($helper, 'getSku')
                        && method_exists($helper, 'getOrderId')
                        && method_exists($helper, 'getVersion')
                        && method_exists($helper, 'getRevision')
                        && method_exists($helper, 'getLicenseKey')
                        && method_exists($helper, 'getPath')) {
                        $extensions[] = array(
                            's' => $helper->getSku(),
                            'o' => $helper->getOrderId(),
                            'v' => $helper->getVersion(),
                            'r' => $helper->getRevision(),
                            'k' => $helper->getLicenseKey(),
                            'p' => $helper->getPath(),
                        );
                    }
                }
            }
        }

        return $extensions;
    }

    private function _getResponse($url, $params)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->write(Zend_Http_Client::POST, $url, '1.1', array(), http_build_query($params, '', '&'));
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);

        return $data;
    }

    private function _saveStatus($value)
    {
        $value = base64_encode(str_rot13(serialize($value)));

        $variable = Mage::getModel('core/variable');
        $variable = $variable->loadByCode(self::STATUS_KEY);

        $variable->setPlainValue($value)
            ->setHtmlValue(Mage::getSingleton('core/date')->gmtTimestamp())
            ->setName(self::STATUS_KEY)
            ->setCode(self::STATUS_KEY)
            ->save();

        return $this;
    }

    private function _getStatus()
    {
        $variable = Mage::getModel('core/variable')->loadByCode(self::STATUS_KEY);
        if ($value = $variable->getPlainValue()) {
            return unserialize(str_rot13(base64_decode($value)));
        }

        return false;
    }

    private function getLicenseKey()
    {
        return $this->k;
    }

    private function getSku()
    {
        return $this->s;
    }

    private function getOrderId()
    {
        return $this->o;
    }

    private function getVersion()
    {
        return $this->v;
    }

    private function getRevision()
    {
        return $this->r;
    }

    private function getPath()
    {
        return $this->p;
    }
}