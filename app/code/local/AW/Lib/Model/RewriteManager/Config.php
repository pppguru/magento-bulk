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

class AW_Lib_Model_RewriteManager_Config
{
    const AW_LIB_BLOCK_REWRITE_MANAGER_VIRTUAL_CLASS = 'AW_Lib_Block_RewriteManager_Virtual_';
    const AW_LIB_MODEL_REWRITE_MANAGER_VIRTUAL_CLASS = 'AW_Lib_Model_RewriteManager_Virtual_';
    const MODEL_CACHE_ID                             = 'AW_Lib_Model_RewriteManager_Config';
    const PREVIOUS_XML_HASH_ID                       = 'AW_Lib_Model_RewriteManager_Config_XML_HASH_ID';
    const VIRTUAL_BLOCKS_CACHE_ID                    = 'AW_Lib_Block_RewriteManager_Virtual';
    const VIRTUAL_MODELS_CACHE_ID                    = 'AW_Lib_Model_RewriteManager_Virtual';

    protected $_xml           = null;
    protected $_isLoadedFlag  = false;

    public function init()
    {
        $config = Mage::getConfig();
        $this->_loadCache();
        if ($this->_isUpdatedXml($config->getXmlString()) || null === $this->_xml && !$this->_isLoadedFlag) {
            $this->_xml = @simplexml_load_string($config->getXmlString(), 'Mage_Core_Model_Config_Element');
            $this
                ->_prepareRewrites()
                ->_saveCache($this, self::MODEL_CACHE_ID)
            ;
        }

        if ($this->_xml instanceof Mage_Core_Model_Config_Element) {
            $config->setXml($this->_xml);
        }

        if (Mage::app()->loadCache(self::VIRTUAL_BLOCKS_CACHE_ID)) {
            include_once 'AW/Lib/Block/RewriteManager/Virtual.php';
        }
        if (Mage::app()->loadCache(self::VIRTUAL_MODELS_CACHE_ID)) {
            include_once 'AW/Lib/Model/RewriteManager/Virtual.php';
        }
        return $this;
    }

    protected function _isUpdatedXml($xmlString)
    {
        $previousXmlHash = Mage::app()->loadCache(self::PREVIOUS_XML_HASH_ID);
        if ($previousXmlHash && $previousXmlHash == md5($xmlString)) {
            return false;
        } else {
            $this->_saveCache(md5($xmlString), self::PREVIOUS_XML_HASH_ID);
        }
        return true;
    }

    protected function _prepareRewrites()
    {
        if (!$this->_xml instanceof Mage_Core_Model_Config_Element || !$this->_xml->aw_lib->rewrite_manager) {
            return $this;
        }

        if ($this->_xml->aw_lib->rewrite_manager->blocks) {
            $blockRewrites = $this->_getRewrites($this->_xml->aw_lib->rewrite_manager->blocks->asArray(),
                'blocks', self::AW_LIB_BLOCK_REWRITE_MANAGER_VIRTUAL_CLASS
            );
            $virtualClasses = $this->_getPreparedVirtualClasses($blockRewrites,
                self::AW_LIB_BLOCK_REWRITE_MANAGER_VIRTUAL_CLASS
            );
            $this->_saveCache($virtualClasses, self::VIRTUAL_BLOCKS_CACHE_ID);
        }
        if ($this->_xml->aw_lib->rewrite_manager->models) {
            $modelRewrites = $this->_getRewrites($this->_xml->aw_lib->rewrite_manager->models->asArray(),
                'models', self::AW_LIB_MODEL_REWRITE_MANAGER_VIRTUAL_CLASS
            );
            $virtualClasses = $this->_getPreparedVirtualClasses($modelRewrites,
                self::AW_LIB_MODEL_REWRITE_MANAGER_VIRTUAL_CLASS
            );
            $this->_saveCache($virtualClasses, self::VIRTUAL_MODELS_CACHE_ID);
        }
        return $this;
    }

    protected function _loadCache()
    {
        $_cacheData = @unserialize(Mage::app()->loadCache(self::MODEL_CACHE_ID));
        if (is_array($_cacheData)) {
            if (array_key_exists('xml', $_cacheData)) {
                $this->_xml = @simplexml_load_string($_cacheData['xml'], 'Mage_Core_Model_Config_Element');
                $this->_isLoadedFlag = true;
            }
        }
        return $this;
    }

    protected function _saveCache($data, $cacheId)
    {
        Mage::app()->saveCache((string)$data, $cacheId, array(), 1800);
        return $this;
    }

    protected function _getRewrites($xmlNodeArray, $groupName, $virtualClass)
    {
        $_result = array();
        foreach ($xmlNodeArray as $rewrite) {
            $_xmlNode = $this->_getXmlNodeByClass($rewrite['rewrite'], $groupName);
            if (null === $_xmlNode) {
                continue;
            }
            $className = $rewrite['rewrite'];
            if ($this->_xml->xpath($_xmlNode)) {
                $className = $this->_xml->xpath($_xmlNode);
            }
            foreach ($rewrite['methods'] as $method) {
                $_result[$className][$rewrite['class']][] = $method;
            }
            $this->_addRewrite($_xmlNode, $virtualClass . $className);
        }
        return $_result;
    }

    protected function _getPreparedVirtualClasses($rewrites, $virtualClass)
    {
        $_virtualClasses = '';
        foreach ($rewrites as $parentClass => $extendClasses) {
            $_finalClassName = $virtualClass . $parentClass;
            $classMethodsString = '';
            $_existMethods = $this->_getPreparedUserCallback($extendClasses);
            $reflection = new ReflectionClass($parentClass);
            foreach ($_existMethods as $methodName => $userCallback) {
                $classMethodsString .= $this->_getClassMethodAsString($reflection, $methodName, $userCallback);
            }
            $_virtualClasses .= 'class ' . $_finalClassName . ' extends ' . $parentClass . ' { ' . $classMethodsString . ' }';
        }
        return $_virtualClasses;

    }

    protected function _getPreparedUserCallback(array $extendClasses)
    {
        $_existMethods = array();
        foreach ($extendClasses as $extendClass => $extendClassMethods) {
            foreach ($extendClassMethods as $method) {
                if (array_key_exists('name', $method)) {
                    $_existMethods[$method['name']][] = array(
                        'class_name'  => $extendClass,
                        'call_before' => $method['call_before'],
                        'call_after'  => $method['call_after'],
                    );
                }
            }
        }
        return $_existMethods;
    }

    protected function _getClassMethodAsString(ReflectionClass $reflection, $methodName, array $userCallback)
    {
        $reflectionMethod = $reflection->getMethod($methodName);
        if (!$reflectionMethod) {
            return '';
        }
        $methodType = 'public';
        if ($reflectionMethod->isPrivate()) {
            $methodType = 'private';
        }

        if ($reflectionMethod->isProtected()) {
            $methodType = 'protected';
        }

        if ($reflectionMethod->isStatic()) {
            $methodType = 'static';
        }
        $reflectionParameters = $reflectionMethod->getParameters();
        $inputParameters = array();
        $parentParameters = array();
        foreach ($reflectionParameters as $param) {
            $paramName = '$' . $param->getName();
            $parentParameters[] = $paramName;
            if ($param->isOptional()) {
                $_defaultValue = $param->getDefaultValue();
                if (is_string($_defaultValue)) {
                    $_defaultValue = '"' . $_defaultValue . '"';
                }

                if (null === $_defaultValue) {
                    $_defaultValue = 'null';
                }

                $paramName .= '=' . $_defaultValue;
            }
            $inputParameters[] = $paramName;
        }

        $templateVars = array(
            '{methodDeclaration}' => $methodType . ' function ' . $methodName . '(' . implode(',', $inputParameters) . ')',
            '{userCallback}' => serialize($userCallback),
            '{parentMethodCallback}' => $methodName . '(' . implode(',', $parentParameters) . ')'
        );
        return strtr($this->_getMethodTemplate(), $templateVars);
    }

    protected function _getMethodTemplate()
    {
        return '{methodDeclaration} {
            $userCallback = unserialize(\'{userCallback}\');
            foreach ($userCallback as $callback) {
                $_userClass = new $callback["class_name"];
                call_user_func_array(array($_userClass, $callback["call_before"]), array($this));
            }
            $_result = parent::{parentMethodCallback};
            foreach ($userCallback as $callback) {
                $_userClass = new $callback["class_name"];
                call_user_func_array(array($_userClass, $callback["call_after"]), array($this, &$_result));
            }
            return $_result;
        }';
    }

    protected function _getXmlNodeByClass($className, $groupName)
    {
        $node = null;
        $classParams = @explode('_', $className);
        if (count($classParams) < 3 || !$this->_xml->xpath('global/' . $groupName)) {
            return $node;
        }

        foreach ($this->_xml->global->$groupName->asArray() as $shortName => $params) {
            if ($params['class'] == $classParams[0] . '_' . $classParams[1] . '_' . $classParams[2]) {
                unset($classParams[0]);
                unset($classParams[1]);
                unset($classParams[2]);
                $node = 'global/' . $groupName . '/' . $shortName . '/rewrite/' . strtolower(implode('_', $classParams));
                break;
            }
        }
        return $node;
    }

    protected function _addRewrite($node, $class)
    {
        $this->_xml->setNode($node, $class, true);
        return $this;
    }

    public function __toString()
    {
        return serialize(
            array(
                'xml' => $this->_xml->asNiceXml('', false),
            )
        );
    }
}