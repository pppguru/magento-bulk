<?php

/**
 * Class MyExtensions
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
class MDN_MyBms_Helper_MyExtensions extends Mage_Core_Helper_Abstract
{
    /**
     * Parsing extensions client side.
     *
     * @return array
     */
    public function getClientExtensions()
    {
        return ((array)Mage::getConfig()->getNode('modules')->children());
    }

    /**
     * Extensions list client side.
     *
     * @return array
     */
    public function listMyExtensions()
    {
        $list = $this->getClientExtensions();

        $listBms = array();
        $ignoredCode = array(
            "MDN_Purchase", "MDN_BackgroundTask", "MDN_CompetitorPrice", "MDN_AdvancedStock", "MDN_ErpApi", "MDN_Organizer", "MDN_HealthyERP", "MDN_SalesOrderPlanning",
            "MDN_Scanner", "MDN_Orderpreparation","MDN_MyBms");

        foreach ($list as $key => $value)
        {

            if (preg_match('/^MDN/', $key) && !in_array($key, $ignoredCode))
            {
                $item = array();

                $item['name'] = "-";
                $item['description'] = "-";
                $item['lastrelease'] = "-";
                $item['status'] = "-";
                $item['version'] = "$value->version";
                $item['code'] = $key;

                $listBms[$key] = $item;
            }
        }


        if (Mage::helper('core')->isModuleEnabled('MDN_HealthyERP'))
        {
            $erpVersion = MDN_HealthyERP_Block_Adminhtml_System_Config_Version::ERP_VERSION;
            $erpArray = array();
            $erpArray['name'] = "";
            $erpArray['description'] = "";
            $erpArray['lastrelease'] = "";
            $erpArray['status'] = "";
            $erpArray['version'] = $erpVersion;
            $erpArray['code'] = "MDN_ERP";
            $listBms['ERP'] = $erpArray;
        }
        return $this->extendExtensionList($listBms);
    }

    /**
     * Extensions list from Json, Version compare, status defined.
     *
     * @param array $lists
     * @return array
     */
    protected function extendExtensionList($lists)
    {

        $json = Mage::helper("MyBms/Webservice")->listBmsExtensions();

        foreach ($json as $key => $value)
        {
            if (isset($lists['MDN_' . $key]))
            {
                $item = $lists['MDN_' . $key];
                $item['description'] = $value->bed_description;
                $item['name'] = $value->bed_name;
                $item['lastrelease'] = $value->bed_version;
                $item['status'] = $this->getVersionMessage($item['version'], $value->bed_version);
                $item['up_to_date'] = ($item['version'] == $value->bed_version);
                $lists['MDN_' . $key] = $item;
            }

            if ($key == "AdvancedStock" && Mage::helper('core')->isModuleEnabled('MDN_HealthyERP'))
            {
                $lists['ERP']['description'] = $value->bed_description;
                $lists['ERP']['name'] = $value->bed_name;
                $lists['ERP']['lastrelease'] = $value->bed_version;
                $lists['ERP']['status'] = $this->getVersionMessage($lists['ERP']['version'], $value->bed_version);
                $item['up_to_date'] = ($lists['ERP']['version'] == $value->bed_version);
            }
        }
        return $lists;
    }

    protected function getVersionMessage($localVersion, $bmsCurrentVersion){

        $status = '';

        $this->adjustVersionLength($localVersion, $bmsCurrentVersion);

        if ($this->compareVersion($localVersion,$bmsCurrentVersion,'=='))
        {
            $status = $this->__("Up to date");
        }

        if ($this->compareVersion($localVersion,$bmsCurrentVersion,'<'))
        {
            $status = $this->__("New version available");
        }

        if ($this->compareVersion($localVersion,$bmsCurrentVersion,'>'))
        {
            $status = $this->__("You are using a Beta version");
        }

        return $status;
    }

    protected function adjustVersionLength(&$localVersion,&$bmsCurrentVersion){
        $localVersionArray = explode(".", $localVersion);
        $bmsCurrentVersionArray = explode(".", $bmsCurrentVersion);
        $nbElementBmsCurrentVersion = count($bmsCurrentVersionArray);
        $nbElementLocalVersion = count($localVersionArray);

        //ex : case 2.9.6 vs 2.9.6.0
        if($nbElementBmsCurrentVersion - $nbElementLocalVersion == 1){
            $localVersion = $localVersion.'.0';
        }

        //ex : case 2.9.6.0 vs 2.9.6
        if($nbElementBmsCurrentVersion - $nbElementLocalVersion == -1){
            $bmsCurrentVersion = $bmsCurrentVersion.'.0';
        }
    }

    protected function compareVersion($localVersion, $bmsCurrentVersion, $operator)
    {
        return version_compare(trim($localVersion),trim($bmsCurrentVersion),$operator);
    }
}