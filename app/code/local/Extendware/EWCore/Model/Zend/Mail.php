<?php
class Extendware_EWCore_Model_Zend_Mail extends Zend_Mail
{
	protected $_replyTo = null;
	private $bcc = array();
	
	public function setReplyTo($email, $name = null)
    {
        if (null !== $this->_replyTo) {
            throw new Zend_Mail_Exception('Reply-To Header set twice');
        }
        // ordering is important here because of difference between zend mail in different version of magento
        parent::setReplyTo($email, $name);
        if (!$this->_replyTo) $this->_replyTo = $this->_filterEmail($email);
        return $this;
    }
    
	public function getReplyTo()
    {
        return $this->_replyTo;
    }
    
	public function addBcc($email)
    {
        if (!is_array($email)) {
            $email = array($email);
        }
		
        foreach ($email as $recipient) {
        	$this->bcc[] = $recipient;
        	parent::addBcc($email);
        }
    	
        return $this;
    }
    
    public function getBcc() {
    	return $this->bcc;
    }
    
	public function getParts() {
    	return $this->_parts;
    }
    
    public function hasAttachments() {
    	return $this->hasAttachments;
    }
    
	public function setHasAttachments($bool) {
    	$this->hasAttachments = $bool;
    	return $this;
    }
}