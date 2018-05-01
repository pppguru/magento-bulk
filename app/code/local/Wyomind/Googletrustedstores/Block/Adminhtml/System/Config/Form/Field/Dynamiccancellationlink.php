<?php

class Wyomind_Googletrustedstores_Block_Adminhtml_System_Config_Form_Field_Dynamiccancellationlink extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $code = Mage::app()->getRequest()->getParam('website');
        if (!empty($code)) {
            $website = Mage::app()->getWebsite(Mage::getConfig()->getNode('websites/' . $code)->system->website->id->asArray());
        } else {
            $websites = Mage::app()->getWebsites();
            $ws = array();
            foreach ($websites as $website) {
                if (count($website->getStores()) > 0) {
                    $ws[] = $website;
                }
            }
            if (count($ws) == 1) {
                $tmp = $ws[0];
                $website = Mage::app()->getWebsite($tmp->getId());
                $code = $tmp->getCode();
            } else {
                return "Please refer to the website configuration";
            }
        }
        $base_url = $website->getConfig('web/unsecure/base_url');

        $file = $website->getConfig("googletrustedstores/cancellations_settings/filename");
        $file = $code . "_" . $file;

        $path = $website->getConfig("googletrustedstores/cancellations_settings/filepath");

        $url = $base_url . $path . '/' . $file;

        $io = new Varien_Io_File();

        $ak = md5(Mage::getStoreConfig("googletrustedstores/license/activation_key"));
        $url_ship = Mage::getUrl('googletrustedstores/generate/cancellations/', array('website' => $website->getId(),'ak'=>$ak));
        
        $html = "$url_ship<br/>";
        
        $url_ship = Mage::helper("adminhtml")->getUrl('googletrustedstores/adminhtml_googletrustedstores/previewcancellations', array('website' => $website->getId()));
        $url_ship_dl = Mage::helper("adminhtml")->getUrl('googletrustedstores/adminhtml_googletrustedstores/previewcancellations', array('dl' => true, 'website' => $website->getId()));
        $html .= "<br/><button onclick='javascript:window.open(\"$url_ship\");return false;'>" . Mage::helper('googletrustedstores')->__('Test feed') . "</button>&nbsp;"
                . "<button onclick='javascript:window.open(\"$url_ship_dl\");return false;'>" . Mage::helper('googletrustedstores')->__('Download feed') . "</button>";



        return $html;
    }

}
