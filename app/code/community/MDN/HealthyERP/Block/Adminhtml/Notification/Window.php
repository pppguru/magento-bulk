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
/**
 * Fix extension conflict
 * 15 Dec 2016, Erik
 */
// class MDN_HealthyERP_Block_Adminhtml_Notification_Window extends Mage_Adminhtml_Block_Notification_Window
class MDN_HealthyERP_Block_Adminhtml_Notification_Window extends AW_All_Block_Notification_Window
{

    public function getErpInfoUrl(){
        return mage::helper('HealthyERP/Probe')->getErpInfoUrl();
    }

    public function isAnHealthyERPNotification($title){
        return $this->containsText('healthyerp',$title);
    }

    public function containsText($searchedText,$text){
        return (strpos($text,$searchedText) !== FALSE);
    }

    public function getObjectUrl()
    {
        $url = $this->_getHelper()->getPopupObjectUrl();
        if($this->isAnHealthyERPNotification($url)){
            $url = $this->getErpInfoUrl();
        }
        return $url;
    }

    public function escapeUrl($url)
    {
        if($this->isAnHealthyERPNotification($url)){
            $url = $this->getErpInfoUrl();
        }
        return $url;
    }


}
