<?php

class MDN_Shipworks_Helper_Request extends Mage_Core_Helper_Abstract {

    public function process($getData) {

        $moduleVersion = "3.1.11.0";
        $schemaVersion = "1.0.0";

        // retrieve the store code
        $storeCode = '';
        if (isset($getData['storecode'])) {
            $storeCode = $getData['storecode'];
        }

        // using output buffering to get around headers that magento is setting after we've started output
        header("Content-Type: text/xml;charset=utf-8");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $secure = false;
        try {
            if (isset($_SERVER['HTTPS'])) {
                $secure = ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == '1');
            }
        } catch (Exception $e) {
            
        }

        // Open the XML output and root
        Mage::helper('Shipworks/Xml')->writeXmlDeclaration();
        Mage::helper('Shipworks/Xml')->writeStartTag("ShipWorks", array("moduleVersion" => $moduleVersion, "schemaVersion" => $schemaVersion));

        try {
            // start the mage engine
            Mage::app($storeCode);
        } catch (Mage_Core_Model_Store_Exception $e) {
            Mage::helper('Shipworks/Xml')->outputError(100, "Invalid Store Code.");
            Mage::helper('Shipworks/Xml')->writeCloseTag("ShipWorks");
            exit;
        }

        // Enforse SSL
        if (!$secure && Mage::getStoreConfig('shipworks/general/require_ssl')) {
            Mage::helper('Shipworks/Xml')->outputError(10, 'A secure (https://) connection is required.');
        } else {

            $isAuthenticated = false;
            try {
                $userName = (isset($getData['username']) ? $getData['username'] : '');
                $password = (isset($getData['password']) ? $getData['password'] : '');
                $key = (isset($getData['key']) ? $getData['key'] : '');
                Mage::helper('Shipworks')->checkAdminLogin($userName, $password, $key);
                $isAuthenticated = true;
            } catch (Exception $ex) {
                Mage::helper('Shipworks/Xml')->outputError(20, $ex->getMessage());
                //die();
            }

            if ($isAuthenticated)
            {
                // If the admin module is installed, we make use of it
                $action = (isset($getData['action']) ? $getData['action'] : '');
                switch (strtolower($action)) {
                    case 'getmodule': 
                        Mage::helper('Shipworks/Action_GetModule')->process();
                        break;
                    case 'getstore': 
                        Mage::helper('Shipworks/Action_GetStore')->process();
                        break;
                    case 'getcount': 
                        Mage::helper('Shipworks/Action_GetCount')->process($storeId);
                        break;
                    case 'getorders': 
                        Mage::helper('Shipworks/Action_GetShipments')->process($storeId);
                        break;
                    case 'getstatuscodes': 
                        Mage::helper('Shipworks/Action_GetStatusCodes')->process();
                        break;
                    case 'updateorder': 
                        Mage::helper('Shipworks/Action_UpdateOrder')->process($getData);
                        break;
                    default:
                        Mage::helper('Shipworks/Xml')->outputError(20, "'$action' is not supported.");
                }
            }
        }

        // Close the output
        Mage::helper('Shipworks/Xml')->writeCloseTag("ShipWorks");
        
    }

}