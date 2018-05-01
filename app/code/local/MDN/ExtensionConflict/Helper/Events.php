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
 * @copyright  Copyright (c) 2015 BoostMyShop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_ExtensionConflict
 */
class MDN_ExtensionConflict_Helper_Events extends Mage_Core_Helper_Abstract
{


    public function getExtensionsEvents(){

        $eventData = array();

        foreach ($this->getScopes() as $scope) {
            $eventData = array_merge(
                $eventData, $this->getScopeEvent($scope)
            );
        }

       return $eventData;
    }


    private function getScopeEvent($scope){

        $config = Mage::getConfig()->getNode($scope . '/events')->children();
        $data = array();

        foreach ($config as $node) {
            $eventName = $node->getName();

            foreach ($node->observers->children() as $observer) {
                $data[$scope.'_'.$eventName][] = array(
                    'name' => $eventName,
                    'class' => Mage::getConfig()->getModelClassName((string) $observer->class),
                    'method' => (string) $observer->method,
                    'scope' => $scope
                );
            }
        }

        uasort($data, array("MDN_ExtensionConflict_Helper_Events", "sortByEvent"));

        return $data;
    }

    private function getScopes(){

        return array(
            'global',
            'frontend',
            'adminhtml',
        );
    }

    public static function sortByEvent($a, $b) {
        $sort = 1;

        if ($a != $b) {
            if ($a < $b)
                $sort = -1;
        }
        return $sort;
    }


}