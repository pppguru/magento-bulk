<?php
class Extendware_EWCore_Block_Adminhtml_Notification_Toolbar extends Extendware_EWCore_Block_Mage_Adminhtml_Template
{
	protected $collection = null;
	protected $outputDisabled = false;
	
	public function _construct() {
		if (Mage::getStoreConfig('advanced/modules_disable_output/' . $this->getModuleName())) {
			Mage::app()->getStore()->setConfig('advanced/modules_disable_output/' . $this->getModuleName(), 0);
			$this->outputDisabled = true;
		}
		parent::_construct();
	}
	
	protected function getAllMessages() {
		$messages = array();
		$messages = array_merge($messages, $this->getAutoloadMessages());
		$messages = array_merge($messages, $this->getDisabledModuleOutputMessages());
		$messages = array_merge($messages, $this->getLeaseExpiringMessages());
		$messages = array_merge($messages, $this->getTrialExpiringMessages());
		$messages = array_merge($messages, $this->getLicenseExpiringMessages());
		$messages = array_merge($messages, $this->getLicenseFailedUpdateMessages());
		$messages = array_merge($messages, $this->getDisabledExtensionMessages());
		$messages = array_merge($messages, $this->getWriteableEtcModulesMessages());
		$messages = array_merge($messages, $this->getInboxMessages());
		$messages = array_merge($messages, $this->getWriteableEtcMessages());
		$messages = array_merge($messages, $this->getWriteableLicensesMessages());
		$messages = array_merge($messages, $this->getWriteableModuleVarFolderMessages());

		return $messages;
	}
	
	public function getMessageCollection()
	{
		if ($this->collection === null) {
			$messages = $this->getAllMessages();
	    	shuffle($messages);
	    	
			$this->collection = new Varien_Data_Collection();
			foreach ($messages as $message) {
				$item = new Varien_Object();
				$item->setData($message);
				$this->collection->addItem($item);
			}
		}
		
		return $this->collection;
	}
	
	protected function getAutoloadMessages() {
		$messages = array();

		if (class_exists('Extendware_EWCore_Model_Autoload', false) === false) {
			$messages[] = array(
					'label' => $this->__('Warning'),
					'text' => '<b>Warning: </b> PHP / Server configuration is preventing a file from loading. Please go to <u>Extendware -> User Guides -> Extendware Core</u> and look at the last troubleshooting item to resolve.'
			);
		}
		return $messages;
	}
	
	protected function getWriteableEtcMessages() {
		$messages = array();
		if ($this->mHelper('config')->isWriteableMessageEnabled() === true) {
			$etcDir = Mage::getConfig()->getOptions()->getEtcDir();
	        $files = glob($etcDir.DS.'*.xml');
	        
	        $showMessage = false;
	        $etcFiles = array();
	        foreach ($files as $file) {
	        	if (strpos(basename($file), 'Extendware_') === 0) {
	        		$etcFiles[] = $file;
	        	}
	        }
	        
	        if (empty($etcFiles) === false) {
		        $showMessage = is_writeable($etcDir) === false || is_readable($etcDir) === false;
				if ($showMessage === false) {
					foreach ($etcFiles as $file) {
						if (is_readable($file) === false or is_writeable($file) === false) {
							$showMessage = true;
							break;
						}
					}
				}
	        }
	        
	        if ($showMessage === true) {
				$messages[] = array(
			    		'label' => $this->__('Warning'), 
			    		'text' => 'Directory and files in <i>[Magento base]/app/etc/</i> needs to be writeable for Extendware Core to function properly.'
				);
			}
		}
		
		return $messages;
	}
	
	protected function getWriteableEtcModulesMessages() {
		$messages = array();
		if ($this->mHelper('config')->isWriteableMessageEnabled() === true) {
			$etcDir = Mage::getConfig()->getOptions()->getEtcDir();
	        $files = glob($etcDir.DS.'modules'.DS.'*.xml');
	        
	        $showMessage = false;
	        foreach ($files as $file) {
	        	if (stripos($file, 'Extendware') !== false) {
	        		if (is_writeable($file) === false or is_readable($file) === false) {
		        		$showMessage = true;
		        		break;
	        		}
	        	}
	        }
	        if ($showMessage === true) {
				$messages[] = array(
		    		'label' => $this->__('Warning'), 
		    		'text' => 'Files in <i>[Magento base]/app/etc/modules/</i> need to be writeable for Extendware Core to function properly.'
	    		);
	        }
		}
			
		return $messages;
	}
	
	protected function getWriteableLicensesMessages() {
		$messages = array();
		if ($this->mHelper('config')->isWriteableMessageEnabled() === true) {
	        $files = @glob(BP.DS.'var' . DS . 'extendware'.DS.'system'.DS.'licenses'.DS.'*.*');
	        $files = @array_merge($files, glob(BP.DS.'var' . DS . 'extendware'.DS.'system'.DS.'serials'.DS.'*.*'));
	        $files = @array_merge($files, glob(BP.DS.'var' . DS . 'extendware'.DS.'system'.DS.'licenses'.DS.'encoder'.DS.'*.*'));
	        
	        $showMessage = false;
	        foreach ($files as $file) {
				if (is_writeable($file) === false or is_readable($file) === false) {
					$showMessage = true;
					break;
				}
	        }
	        if ($showMessage === true) {
	        	if ($this->mHelper('config')->isWhiteLabeled() === false) {
					$messages[] = array(
			    		'label' => $this->__('Warning'), 
			    		'text' => 'Files / directories in <i>[Magento base]/var/extendware/system/</i> need to be writeable for Extendware Core to function properly.'
		    		);
	        	} else {
	        		$messages[] = array(
			    		'label' => $this->__('Warning'), 
			    		'text' => 'Files / directories in <i>[Magento base]/var/</i> need to be writeable for Extendware Core to function properly.'
		    		);
	        	}
	        }
		}
			
		return $messages;
	}
	
	protected function getWriteableModuleVarFolderMessages() {
		$messages = array();
		if ($this->mHelper('config')->isWriteableMessageEnabled() === true) {
			$baseDirectory = Mage::getConfig()->getOptions()->getVarDir() . DS . 'extendware';
			$directories = $this->mHelper('file')->scanDir($baseDirectory);

			$showMessage = false;
	        foreach ($directories as $directory) {
	        	$directory = $baseDirectory . DS. $directory;
				if (is_writeable($directory) === false or is_readable($directory) === false) {
					$showMessage = true;
					break;
				}
	        }
	        if ($showMessage === true) {
				$messages[] = array(
		    		'label' => $this->__('Warning'), 
		    		'text' => 'Directories in <i>[Magento base]/var/extendware/</i> need to be writeable for extensions to function properly.'
	    		);
	        }
		}
			
		return $messages;
	}
	
	protected function getInboxMessages() {
		$messages = array();
		$collection = $this->mHelper('notification')->getMessageCollection();

		if ($collection->count() > 0) {
			$types = array();
			foreach ($collection as $m) {
				if (isset($types[$m->getSeverity()]) === false) {
					$types[$m->getSeverity()] = 0;
				}
				$types[$m->getSeverity()]++;
			}
			
			$item = $this->mHelper('notification')->getMessage();
			if ($item) {			
				$statParts = array();
				if (isset($types['critical'])) {
		            $statParts[] = '<span class="critical"><strong>'.$types['critical'].'</strong> '.$this->__('critical').'</span>';
				}
				foreach (array('major', 'minor', 'notice') as $type) {
					if (isset($types[$type])) $statParts[] = '<strong>'.$types[$type].'</strong> '.$this->__($type);
				}
	       		
				$stats = '';
		        $c = count($statParts);
		        for ($i = 0; $i < $c; $i++) {
		            $stats .= $statParts[$i] . ($i == $c-1 ? '' : ($i == $c-2 ? $this->__(' and ') : ', '));
		        }
				
				$messages[] = array(
		    		'label' => $this->__('Message'), 
		    		'text' => $item->getSubject(),
			    	'info_url' => $this->getUrl('adminhtml/ewcore_message/edit', array('id' => $item->getId())),
					'info_url_on_click' => $item->getUrl() ? "this.target='_blank';" : '',
					'right' => $this->__('You have %s unread message(s). <a href="%s">Go to messages inbox</a>', $stats, $this->getUrl('adminhtml/ewcore_message/index'))
	    		);
			}
		}
		
		return $messages;
	}
	
	protected function getDisabledModuleOutputMessages() {
		$messages = array();
		$showMessage = $this->outputDisabled === true;
		$moduleCollection = Mage::getSingleton('ewcore/module')->getCollection();
		foreach ($moduleCollection as $module) {
    		if ($module->isExtendware() and $module->isActive() === true) {
    			if (Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $module->getId())) {
    				$showMessage = true;
    				break;
    			}
    		}
    	}
		
    	if ($showMessage === true) {
    		$messages[] = array(
	    		'label' => $this->__('Module Output Disabled'), 
	    		'text' => $this->__('<b>Warning: </b>There are Extendware extensions that have their module output disabled in System -> Configuration -> Advanced. You can disable extensions in Extendware -> Manage Extensions -> Overview if needed.'),
		    	'info_url' => $this->getUrl('adminhtml/system_config/edit/section/advanced'),
    		);
    	}
		
		return $messages;
	}
	
	protected function getDisabledExtensionMessages() {
		$messages = array();
		if ($this->mHelper('config')->isDisabledExtensionsMessageEnabled() === true) {
			$numDisabledModules = 0;
			$moduleCollection = Mage::getSingleton('ewcore/module')->getCollection();
			foreach ($moduleCollection as $module) {
	    		if ($module->isExtendware() and $module->isActive() === false and $module->isForMainSite() === false) {
	    			$numDisabledModules++;
	    		}
	    	}
			
	    	if ($numDisabledModules >= $this->mHelper('config')->getDisabledExtensionsMinNum()) {
	    		$messages[] = array(
		    		'label' => $this->__('Extensions Disabled'), 
		    		'text' => $this->__('There are %s Extendware extensions installed that are currently disabled', $numDisabledModules),
			    	'info_url' => $this->getUrl('adminhtml/ewcore_module/index'),
	    			'configure_url' => $this->getUrl('adminhtml/ewcore_config/', array('section' => 'ewcore_messaging'))
	    		);
	    	}
		}
		
		return $messages;
	}
	
	protected function getLicenseFailedUpdateMessages() {
		$messages = array();
		if ($this->mHelper('config')->isLicenseFailedUpdateMessageEnabled() === true) {
			$failedUpdates = array();
			$numFailures = 0;
			$moduleCollection = Mage::getSingleton('ewcore/module')->getCollection();
			foreach ($moduleCollection as $module) {
	    		if ($module->isActive() === true and $module->isExtendware() === true and $module->isForMainSite() === false) {
	    			if ($module->isLicensed() === false) continue;
	    			
	    			$numFailures = max($numFailures, (int)$this->mHelper('config')->getLicensingNumUpdateFailures($module->getId()));
	    		}
	    	}
	    	
			$daysSinceInstallation = @floor(filemtime(BP.DS.'app'.DS.'etc'.DS.'modules'.DS.'Extendware_EWCore.xml')/86400);
	    	if ($numFailures >= 3 and $daysSinceInstallation >= 10) {
	    		$scripts = array();
	    		if ($numFailures > 5) $scripts[] = "Effect.Pulsate('license_failed_update_message', {duration: '3', from: 0.5, pulses: 1})";
	    		$messages[] = array(
	    			'id' => 'license_failed_update_message',
	    			'label' => $this->__('License / Serials Update Issue'), 
	    			'text' => $this->__('Licenses / serials have failed to update %s times. <b>Failing to fix this will cause your Extendware extensions to disable</b>', $numFailures),
	    			'info_url' => $this->getUrl('adminhtml/ewcore_content_page/view', array('id' => 'license_troubleshooting')),
    				'info_label' => '<b>' . $this->__('Resolve this issue (click here)') . '</b>',
	    			'configure_url' => $this->getUrl('adminhtml/ewcore_config/', array('section' => 'ewcore_messaging')),
	    			'script' => join ('; ', $scripts)
	    		);
	    	}
    	}
		
		return $messages;
	}
	
	protected function getLicenseExpiringMessages() {
		$messages = array();
		if ($this->mHelper('config')->isLicenseExpiringMessageEnabled() === true) {
			$numDaysBeforeExpiration = 15;
	    	$minExpirationTime = time() + 60*60*24*999999;
			$moduleCollection = Mage::getSingleton('ewcore/module')->getCollection();
			foreach ($moduleCollection as $module) {
	    		if ($module->isActive() === true and $module->isExtendware() === true and $module->isForMainSite() === false) {
	    			if ($module->isLicensed() === false) continue;
	    			if ($module->getLicense()->getExpiry()> 0) $minExpirationTime = min($minExpirationTime, $module->getLicense()->getExpiry());
	    			if ($module->getEncoderLicense()->getExpiry() > 0) $minExpirationTime = min($minExpirationTime, $module->getEncoderLicense()->getExpiry());
	    		}
	    	}
			
	    	$minExpirationDelta = max(0, floor(($minExpirationTime - time())/(60*60*24)));
	    	if ($minExpirationDelta <= $numDaysBeforeExpiration) {
	    		$scripts = array();
				$scripts[] = "Effect.Pulsate('license_expiring_message', {duration: '5', from: 0.5, pulses: 5})";
	    		if ($minExpirationDelta <= 10) $scripts[] = "Effect.Shake('license_expiring_message', {duration: 3})";
				$messages[] = array(
	    			'id' => 'license_expiring_message',
	    			'label' => $this->__('License / Serials Update Issue'), 
	    			'text' => $this->__('Licenses / serials <u>will expire</u> in %s day(s) or less. <b>Failing to fix this will cause your Extendware extensions to disable</b>. Please click read more for more info.', (int) $minExpirationDelta),
		    		'info_url' => $this->getUrl('adminhtml/ewcore_content_page/view', array('id' => 'license_troubleshooting')),
    				'info_label' => '<b>' . $this->__('Resolve this issue (click here)') . '</b>',
					'configure_url' => $this->getUrl('adminhtml/ewcore_config/', array('section' => 'ewcore_messaging')),
	    			'script' => join ('; ', $scripts)
				);
	    	}
    	}
		
		return $messages;
	}
	
	protected function getTrialExpiringMessages() {
		$messages = array();
		$numDaysBeforeExpiration = 15;
    	$minExpirationTime = time() + 60*60*24*999999;
		$moduleCollection = Mage::getSingleton('ewcore/module')->getCollection();
		foreach ($moduleCollection as $module) {
    		if ($module->isActive() === true and $module->isExtendware() === true and $module->isForMainSite() === false) {
    			if ($module->isLicensed() === false) continue;
    			if (in_array($module->getLicense()->getType(), array('trial', 'demo')) === false) continue;
    			if ($module->getLicense()->getLeaseExpiry() > 0) $minExpirationTime = min($minExpirationTime, $module->getLicense()->getLeaseExpiry());
    		}
    	}

    	$minExpirationDelta = max(0, floor(($minExpirationTime - time())/(60*60*24)));
    	if ($minExpirationDelta <= $numDaysBeforeExpiration) {
    		$scripts = array();
    		if ($minExpirationDelta <= 3) $scripts[] = "Effect.Shake('trial_expiring_message', {duration: 3})";
    		if ($this->mHelper('config')->isWhiteLabeled() === false) {
				$messages[] = array(
	    			'id' => 'trial_expiring_message',
	    			'label' => $this->__('Trial Expiring'), 
	    			'text' => $this->__('Extension trial will end in %d day(s). <b>Please purchase the Extendware extension to continue using it.</b>', (int) $minExpirationDelta),
		    		'info_url' => 'http://www.extendware.com/rwcore/redirect/normal/to/purchase_trial/',
    				'info_label' => '<b>' . $this->__('Purchase Trial') . '</b>',
					'configure_url' => $this->getUrl('adminhtml/ewcore_config/', array('section' => 'ewcore_messaging')),
	    			'script' => join ('; ', $scripts)
				);
    		} else {
    			$messages[] = array(
	    			'id' => 'lease_expiring_message',
	    			'label' => $this->__('Plan Expiring'), 
	    			'text' => $this->__('Extension trial will end in %d day(s). <b>Please purchase the Extendware extension to continue using it.</b>', (int) $minExpirationDelta),
	    			'script' => join ('; ', $scripts)
				);
    		}
    	}
		
		return $messages;
	}
	
	protected function getLeaseExpiringMessages() {
		$messages = array();
		$numDaysBeforeExpiration = 20;
    	$minExpirationTime = time() + 60*60*24*999999;
		$moduleCollection = Mage::getSingleton('ewcore/module')->getCollection();
		foreach ($moduleCollection as $module) {
    		if ($module->isActive() === true and $module->isExtendware() === true and $module->isForMainSite() === false) {
    			if ($module->isLicensed() === false) continue;
    			if (in_array($module->getLicense()->getType(), array('normal', 'promotion', 'gift')) === false) continue;
    			if ($module->getLicense()->getLeaseExpiry() > 0) $minExpirationTime = min($minExpirationTime, $module->getLicense()->getLeaseExpiry());
    		}
    	}

    	$minExpirationDelta = max(0, floor(($minExpirationTime - time())/(60*60*24)));
    	if ($minExpirationDelta <= $numDaysBeforeExpiration) {
    		$scripts = array();
    		if ($minExpirationDelta <= 10) $scripts[] = "Effect.Shake('lease_expiring_message', {duration: 3})";
    		if ($this->mHelper('config')->isWhiteLabeled() === false) {
				$messages[] = array(
	    			'id' => 'lease_expiring_message',
	    			'label' => $this->__('Plan Expiring'), 
	    			'text' => $this->__('Extension upgrade / support / access selected when ordering the extension will end in %d day(s). <b>Please extend your Extendware plan to continue using the extension.</b>', (int) $minExpirationDelta),
		    		'info_url' => 'http://www.extendware.com/rwcore/redirect/normal/to/renew_yearly_plan/',
    				'info_label' => '<b>' . $this->__('Resolve this issue (click here)') . '</b>',
					'configure_url' => $this->getUrl('adminhtml/ewcore_config/', array('section' => 'ewcore_messaging')),
	    			'script' => join ('; ', $scripts)
				);
    		} else {
    			$messages[] = array(
	    			'id' => 'lease_expiring_message',
	    			'label' => $this->__('Plan Expiring'), 
	    			'text' => $this->__('Extension upgrade / support / access will end in %d day(s). <b>Please contact Extendware to renew your plan.</b>', (int) $minExpirationDelta),
	    			'script' => join ('; ', $scripts)
				);
    		}
    	}
		
		return $messages;
	}
	
	public function getMessageCount()
	{
		return (int) $this->getMessageCollection()->count();
	}
	
	public function canShow()
	{
		return (bool) ($this->getMessageCollection()->count() > 0);
	}
}