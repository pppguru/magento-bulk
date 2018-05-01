<?php

class MDN_Scanner_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isMobileUserAgent()
        {
            $isMobileUserAgent = false;
            $mobileUserAgent = mage::getStoreConfig('scanner/display/mobile_user_agent');
            if ($mobileUserAgent)
            {
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                $isMobileUserAgent = preg_match('/'.$mobileUserAgent.'/', $userAgent);
            }
            return $isMobileUserAgent;
        }
}