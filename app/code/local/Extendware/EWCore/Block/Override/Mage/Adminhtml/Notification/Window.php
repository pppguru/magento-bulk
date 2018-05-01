<?php
class Extendware_EWCore_Block_Override_Mage_Adminhtml_Notification_Window extends Extendware_EWCore_Block_Override_Mage_Adminhtml_Notification_Window_Bridge
{
	protected $showExtendwarePopup = false;
	public function __construct() {
		$ewcoreLatestNotice = Mage::helper('ewcore/notification')->getLatestMessage();
		$lastMageNoticeTime = strtotime($this->_getHelper()->getLatestNotice()->getDateAdded());
		$lastEWCoreNoticeTime = max(strtotime($ewcoreLatestNotice->getCreatedAt()), strtotime($ewcoreLatestNotice->getSentAt()));

		if ($lastEWCoreNoticeTime > $lastMageNoticeTime or mt_rand(1, 100) <= 10) {
			$this->showExtendwarePopup = true;
		}
		
		if (!$this->getLastNotice()) {
			$this->showExtendwarePopup = false;
		}
		
		parent::__construct();
		if ($this->showExtendwarePopup) {
			$this->setNoticeMessageText($this->getLastNotice()->getTitle());
		}
	}
	
	public function isShow()
    {
    	if ($this->showExtendwarePopup === false) {
    		return parent::isShow();
    	}
    	
        if (!$this->isOutputEnabled('Mage_AdminNotification')) {
            return false;
        }
        if ($this->getRequest()->getControllerName() == 'notification') {
            return false;
        }
       
        if (!$this->getLastNotice()) {
        	return false;
        }

        return true;
    }
    
	public function getLastNotice()
    {
    	if ($this->showExtendwarePopup === false) {
    		return parent::getLastNotice();
    	}
    	
    	static $lastNotice = false;
    	if ($lastNotice === false) {
    		$lastNotice = null;
    		
    		$message = Mage::helper('ewcore/notification')->getMessage();
    		if ($message) {
    			$lastNotice = new Varien_Object();
    			$lastNotice->setTitle($message->getSubject());
    			$lastNotice->setDescription($message->getSummary());
    			$lastNotice->setUrl($this->getUrl('adminhtml/ewcore_message/edit', array('id' => $message->getId())));
    			$lastNotice->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
    			
    			switch ($message->getSeverity()) {
		            case 'notice':
		                $lastNotice->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
		                break;
		            case 'minor':
		                $lastNotice->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
		                break;
		            case 'major':
		               $lastNotice->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR);
		                break;
		            case 'critical':
		                $lastNotice->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL);
		                break;
		        }
    		}
    	}
    	
    	return $lastNotice;
    }
}
