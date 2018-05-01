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
 * @copyright  Copyright (c) 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_ExtensionConflict
 */
class MDN_ExtensionConflict_Helper_ObjectTrace extends Mage_Core_Helper_Abstract
{

    /**
     * Return the complete stacktrace of a magento Model, Helper, Block, without using it in his context
     *
     * @param $coreClass
     * @param $coreModule
     * @return string $stackTrace
     */
    public function getObjectTrace($coreClass, $coreModule)
    {
        $stackTrace = '';

        $objectInstance = $this->getObjectInstance($coreClass, $coreModule);

        if ($objectInstance){
            $stackTrace = $this->getStackTraceFromInstance($objectInstance);
        }

        return $stackTrace;
    }

    public function getStackTraceFromInstance($objectInstance){
        $stackTrace = '';

        try {
            $className = get_class($objectInstance);
            $class = new ReflectionClass($className);
            $parents = array();
            $limit = 100;//security to avoid infinite loop
            while ($parent = $class->getParentClass()) {
                $limit--;
                if($limit == 0)
                    break;
                $parents[] = $parent->getName();
                $class = $parent;
            }
            $stackTrace = '<br><pre>'.implode("<br> - ", $parents).'</pre>';
        } catch (Exception $ex) {
        }

        return $stackTrace;
    }

    public function getObjectInstance($coreClass, $coreModule){

        $objectInstance = null;

        try {
            if (strpos($coreClass, 'models_') !== FALSE) {
                $coreClass = str_replace('models_', '', $coreClass);
                $objectInstance = mage::getModel($coreModule . '/' . $coreClass);
            }
            if (strpos($coreClass, 'helpers_') !== FALSE) {
                $coreClass = str_replace('helpers_', '', $coreClass);
                $objectInstance = mage::helper($coreModule . '/' . $coreClass);
            }
            if (strpos($coreClass, 'blocks_') !== FALSE) {
                $coreClass = str_replace('blocks_', '', $coreClass);
                $objectInstance = Mage::getSingleton('core/layout')->createBlock($coreModule . '/' . $coreClass);
            }
        } catch (Exception $ex) {
            //ignore
        }
        return $objectInstance;
    }

}