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
 * @copyright  Copyright (c) 2013 Boostmyshop (http://www.boostmyshop.com)
 * @author : Guillauem SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_HealthyERP_Block_Adminhtml_System_Config_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    //CURRENT ERP VERSION
    const ERP_VERSION = '2.9.7.0';

    /**
     * Display the XML + Installed version of each sub modules OF ERP
     *
     * If there is a version difference,
     *  the module is display in red (install problem during the SQL setup)
     *  Else the module is display in black
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {    
        //Get conf version from XML files
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;

        $html = '<p><b>ERP : '.self::ERP_VERSION.'</b></p>';
        $prefix = 'MDN_';
        
        $ERP_Modules = array('AdvancedStock','BackgroundTask','HealthyERP','Orderpreparation','Organizer','Purchase','SalesOrderPlanning','Scanner','SmartReport','ErpApi');

        //display the 2 versions for each modules
        foreach ($ERP_Modules as $modName){
          $realName = $prefix.$modName;

          //Get installed version from CORE RESOURCE TABLE
           if(array_key_exists($realName,$modulesArray)) {
                $installedVersion = $this->getVersionFromCoreResource($modName);
                $confVersion = $modulesArray[$realName]->version;
                $codePool = $modulesArray[$realName]->codePool;
                $color = 'black';
                if ($installedVersion && $installedVersion < $confVersion) {
                    $color = 'red';
                }
                $versionDisplay = '<font color="' . $color . '">' . $confVersion . ' (' . $installedVersion . ')</font>';
                $html .= '<p>' . $modName . ' : ' . $versionDisplay . ' - <i>' . $codePool . '</i></p>';
           }
        }

        //Magento Alerts
        if (Mage::getStoreConfig('healthyerp/options/display_notifications')){
            mage::helper('HealthyERP/Probe')->checkAndNotify();
        }

        return $html;
    }

    /**
     * Get the version installed in the table core_resource
     * The version is relative with the install success of the script presents in the /sql folder of each module
     * @param type $modName
     * @return type
     */
    protected function getVersionFromCoreResource($modName){
       $postfix = '_setup';
       $tablePrefix = mage::getModel('BackgroundTask/Constant')->getTablePrefix();
       $sql = "select version from ".$tablePrefix."core_resource where code='".$modName.$postfix."'";
       $version = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
       return $version;
    }   
    
}