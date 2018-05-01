<?php

class MDN_Shipworks_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * 
     * @param type $targetVersion
     * @return boolean
     */
    function MagentoVersionGreaterOrEqualTo($targetVersion) {
        $mageVersion = Mage::getVersion();

        $currentParts = preg_split('[\.]', $mageVersion);
        $targetParts = preg_split('[\.]', $targetVersion);

        $i = 0;
        foreach ($currentParts as $currentPart) {
            if ($i >= count($targetParts)) {
                // gotten this far, means that current version of 1.4.0.1 > target version 1.4.0
                return true;
            }

            $targetPart = $targetParts[$i];

            // if this iteration's target version part is greater than the magento version part, we're done.
            if ((int) $targetPart > (int) $currentPart) {
                return false;
            } else if ((int) $targetPart < (int) $currentPart) {
                // the magento version part is greater, then we're done
                return true;
            }


            // otherwise to this point the two are equal, continue
            $i++;
        }

        // got this far means the two are equal
        return true;
    }

    /**
     * Check to see if admin functions exist.  And if so, determine if the user has access
     * @return boolean
     */
    function checkAdminLogin($username, $password, $key) {
        if (!Mage::getSingleton('admin/session')->isLoggedIn())
        {
            $user = Mage::getSingleton('admin/session')->login($username, $password);
            if ((!$user) || (!$user->getId())) {
                throw new Exception("The username or password is incorrect.");
            }
        }
        return true;
    }

    /**
     * 
     * @param type $sqlUtc
     * @return type
     */
    function toLocalSqlDate($sqlUtc) {
        $pattern = "/^(\d{4})-(\d{2})-(\d{2})\T(\d{2}):(\d{2}):(\d{2})$/i";

        if (preg_match($pattern, $sqlUtc, $dt)) {
            $unixUtc = gmmktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]);

            return date("Y-m-d H:i:s", $unixUtc);
        }

        return $sqlUtc;
    }

    /**
     * Converts a sql data string to xml date format
     * @param type $dateSql
     * @return type
     */
    function FormatDate($dateSql) {
        $pattern = "/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/i";

        if (preg_match($pattern, $dateSql, $dt)) {
            $dateUnix = mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]);
            return gmdate("Y-m-d\TH:i:s", $dateUnix);
        }

        return $dateSql;
    }
    
}