<?php
abstract class Extendware_EWCore_Model_Mage_Core_Session extends Mage_Core_Model_Session
{
	public function __construct()
    {
        $this->init('core_' . get_class($this));
    }
    
	public function addMessage(Mage_Core_Model_Message_Abstract $message)
    {
    	// ensure duplicate messages are not added
    	$identifier = md5($message->getType() . '-' . $message->getText() . '-' . $message->getCode() . '-' . $message->getIdentifier() . '-' . (int)$message->getIsSticky());
    	$messages = $this->getMessages()->getItems();
    	foreach ($messages as $m) {
    		$identifier2 = md5($m->getType() . '-' . $m->getText() . '-' . $m->getCode() . '-' . $m->getIdentifier() . '-' . (int)$m->getIsSticky());
    		if ($identifier == $identifier2) {
    			return $this;
    		}
    	}

		return parent::addMessage($message);;
    }
}
