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
class MDN_HealthyERP_Helper_Probe extends Mage_Core_Helper_Abstract {

    const CLASS_BASE = 'MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_';

    private $_testableProbeListNOK = array(
        'CheckAttributes',
        );

    //refactored

    //disabled, will generate too much alert
    //'Backgroundtasks',
    //'Ordernotconsideredyet',

    private $_testableProbeList = array(
        'MissingProductAvailibilityStatus',
        'UnconsistantProductAvailibilityStatus',
        'QtyDifferentSumStockMovements',
        'UnconsistantQtyOrdered',
        'UnconsistantWaitingForDeliveryQty',
        'QtyNegative',
        'Views',
        'CheckOptions',
    );

    /**
     * Give the list of probe checkable by the cron
     *
     * @return array
     */
    public function getProbeList(){
        $probeList = array();
        foreach($this->_testableProbeList as $probeName){
            $probeList[] = self::CLASS_BASE.$probeName;
        }
        return $probeList;
    }

    /**
     * mage::helper('HealthyERP/Probe')->callProbeAction($probeClassName,$action, $param);
     *
     * @param $probeClassName
     * @param $action
     * @param $param
     * @param $staticCall
     * @return mixed|null
     */
    public function callProbeAction($probeClassName, $action, $param = null){
        $return = null;

        if(!empty($probeClassName) && !empty($action)){
            $return = call_user_func($probeClassName.'::'.$action,$param);
        }
        return $return;
    }

    public function checkProbes()
    {
        $probesWithProblems = array();

        $probeList = $this->getProbeList();

        foreach($probeList as $probeClass){
            $errorList = $this->callProbeAction($probeClass,'getErrorsList');
            $result = $this->callProbeAction($probeClass,'getErrorStatus',count($errorList));
            if($result == MDN_HealthyERP_Block_Adminhtml_System_Config_Probe_Abstract::STATUS_NOK){
                $probesWithProblems[] = $probeClass;
            }
        }

        return $probesWithProblems;
    }

    public function getErpInfoUrl(){
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/', array('section' => 'healthyerp'));
    }


    private function notifyBackOfficeAlert($nbIssues){
        $severity = 1;
        $text = $this->__("ERP has detected %s issue(s). Please go into System > Configuration > ERP > ERP Info",$nbIssues);
        Mage::getModel('AdminNotification/Inbox')->add($severity, $text, $text, $this->getErpInfoUrl());
    }

    public function checkAndNotify(){
        $probeList = $this->checkProbes();
        $nbIssues = count($probeList);
        if ($nbIssues>0){
            $this->notifyBackOfficeAlert($nbIssues);
        }else{
            $this->removePreviousBackOfficeAlert();
        }
    }

    public function removePreviousBackOfficeAlert(){
        $collection  = Mage::getModel('AdminNotification/Inbox')
            ->getCollection()
            ->addFieldToFilter( 'url', array( "like" =>'%healthyerp%' ));

        foreach($collection as $inboxErpMessage){
            $inboxErpMessage->delete();
        }
    }

}
