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
class MDN_HealthyERP_Block_Adminhtml_Notification_Toolbar extends Mage_Adminhtml_Block_Notification_Toolbar
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

    public function getLatestNoticeUrl()
    {
        $url = $this->_getHelper()->getLatestNotice()->getUrl();
        if($this->isAnHealthyERPNotification($url)){
            $url = $this->getErpInfoUrl();
        }
        return $url;
    }

}