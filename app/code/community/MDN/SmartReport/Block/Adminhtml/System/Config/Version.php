<?php

class MDN_SmartReport_Block_Adminhtml_System_Config_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->getVersionFromCoreResource('SmartReport');
    }

    /**
     * Get the version installed in the table core_resource
     * The version is relative with the install success of the script presents in the /sql folder of each module
     * @param type $modName
     * @return type
     */
    protected function getVersionFromCoreResource($modName){
        $postfix = '_setup';
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $sql = "select version from ".$tablePrefix."core_resource where code='".$modName.$postfix."'";
        $version = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
        return $version;
    }

}