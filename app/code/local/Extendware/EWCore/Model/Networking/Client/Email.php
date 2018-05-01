<?php
class Extendware_EWCore_Model_Networking_Client_Email extends Extendware_EWCore_Model_Varien_Object
{
	protected $mail;

	protected function getTransport()
	{
		if ($this->getReturnPath() !== null) {
			// this can be swapped with Zend_Mail_Transport_Smtp: http://framework.zend.com/manual/1.12/en/zend.mail.smtp-authentication.html
            // this is only needed if you need to use smtp to send mail:
            // example of using smtp
			//return new Zend_Mail_Transport_Smtp('mail.server.com', array('auth' => 'login', 'username' => 'myusername', 'password' => 'password'));
			return new Zend_Mail_Transport_Sendmail('-f'.$this->getReturnPath());
        }
        
        return null;
	}
	
    protected function getMail()
    {
        if (is_null($this->mail)) {
            $this->mail = new Extendware_EWCore_Model_Zend_Mail('utf-8');
        }
        return $this->mail;
    }

	public function sendHtml($name = null, $email = null, $subject = null, $body = null)
    {
        return $this->send('html', $name, $email, $subject, $body);
    }

    public function sendPlain($name = null, $email = null, $subject = null, $body = null)
    {
        return $this->send('plain', $name, $email, $subject, $body);
    }
    
    protected function isValidForSend()
    {
    	return (bool)($this->getType() and $this->getToName() and $this->getToEmail() and $this->getFromName() and $this->getFromEmail() and $this->getSubject() and $this->getBody());
    }
    
    public function setForwardExceptions($bool) {
    	return $this->setData('forward_exceptions', (bool)$bool);
    }
    
    protected function processSend()
    {
    	$this->prepareMail();
		$mail = $this->getMail();

		try {
            $mail->send($this->getTransport());
            $this->mail = null;
        } catch (Exception $e) {
            $this->mail = null;
            Mage::logException($e);
            $this->setLastException($e);
            if ($this->getForwardExceptions() === true) {
            	throw $e;
            }
            return false;
        }

        return true;
    }
    
    protected function prepareMail()
    {
    	$mail = $this->getMail();
    	if ($this->getReplyTo()) $mail->setReplyTo($this->getReplyTo());
    	// this can trigger issues on 3rd party emails such as amazon
    	//if ($this->getReturnPath()) $mail->setReturnPath($this->getReturnPath());
        
    	$emails = $this->getToEmails();
    	$names = $this->getToNames();
		foreach ($emails as $key => $email) {
        	$mail->addTo($email, '=?utf-8?B?'.base64_encode($names[$key]).'?=');
        }
        
        $bcc = $this->getBcc();
        foreach ($bcc as $email) {
        	$mail->addBcc($email);
        }
        
        $mail->setSubject('=?utf-8?B?'.base64_encode($this->getSubject()).'?=');
        $mail->setFrom($this->getFromEmail(), $this->getFromName());
        
        if ($this->getType() == 'plain') $mail->setBodyText($this->getBody());
        else $mail->setBodyHTML($this->getBody());
        
        return $this;
    }
    
    public function send($type = null, $name = null, $email = null, $subject = null, $body = null)
    {
    	if ($type) $this->setType($type);
    	if ($name) $this->setToName($name);
    	if ($email) $this->setToEmail($email);
    	if ($subject) $this->setSubject($subject);
    	if ($body) $this->setBody($body);
    	
    	if ($this->isValidForSend() === false) {
    		Mage::logException(new Exception('This e-mail is not valid for send. There is missing or invalid information.')); // translation is intentionally omitted
            return false;
        }
        
        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host', $this->getStoreId()));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port', $this->getStoreId()));
        
        return $this->processSend();
	}
	
	public function getStoreId() {
		return (int) $this->getData('store_id');
	}
	
	public function getType()
	{
		if (strtolower($this->getData('type')) == 'plain') {
			return 'plain';
		}
		
		return 'html';
	}
	
	public function getToName()
	{
		if ($this->getData('to_name')) return $this->getData('to_name');
		else {
			$email = $this->getToEmail();
			if (is_string($email) and strpos($email, '@') > 0) {
				return substr($email, 0, strpos($email, '@'));
			}
		}
		
		return null;
	}
	
	public function setToName($name)
	{
		return $this->setData('to_name', $name);
	}
	
	public function setToEmail($email)
	{
		return $this->setData('to_email', $email);
	}
	
	protected function extractEmails($emails) {
		$result = array();
		$emails = (array)$emails;
		foreach ($emails as $key => $emailGroup) {
        	$emailGroup = trim($emailGroup);
        	$emailGroup = preg_split('/\s*(?:,|;)\s*/', $emailGroup);
        	foreach ($emailGroup as $email) {
        		$email = trim($email);
        		if ($email) $result[] = $email;
        	}
        }

        return $result;
	}
	
	public function getToEmails()
	{
        return $this->extractEmails($this->getToEmail());
	}
	
	public function setBcc($email)
	{	
		return $this->setData('bcc', $email);
	}
	
	public function getBcc()
	{
        return $this->extractEmails($this->getData('bcc'));
	}
	
	public function getToNames()
	{
		$result = array();
		$emails = $this->getToEmails();
    	$names = array_values((array)$this->getToName());
		foreach ($emails as $key => $email) {
        	$email = trim($email);
        	$result[] = trim(isset($names[$key]) ? $names[$key] : substr($email, 0, strpos($email, '@')));
        }
        
        return $result;
	}
	
	public function setSubject($subject)
	{
		return $this->setData('subject', trim($subject));
	}
	
	public function setBody($body)
	{
		return $this->setData('body', trim($body));
	}
	
	public function getFromEmail()
	{
		if ($this->getData('from_email')) return $this->getData('from_email');
		else if ($this->getData('reply_to')) return $this->getData('reply_to');
		else if ($this->getData('return_path')) return $this->getData('return_path');
		return null;
	}
	
	public function getFromName()
	{
		if ($this->getData('from_name')) return $this->getData('from_name');
		else {
			$email = $this->getFromEmail();
			if (is_string($email) and strpos($email, '@') > 0) {
				return substr($email, 0, strpos($email, '@'));
			}
		}
		
		return null;
	}
	
	public function getReplyTo()
	{
		if ($this->getData('reply_to')) return $this->getData('reply_to');
		else if ($this->getData('from_email')) return $this->getData('from_email');
		else if ($this->getData('return_path')) return $this->getData('return_path');
		return null;
	}
	
	public function getReturnPath()
	{
		if ($this->getData('return_path')) return $this->getData('return_path');
		else if ($this->getData('reply_to')) return $this->getData('reply_to');
		else if ($this->getData('from_email')) return $this->getData('from_email');
		return null;
	}

	public function setStoreId($id)
    {
    	return $this->setData('store_id', (int)$id);
    }
    
    public function setReturnPath($email)
    {
    	return $this->setData('return_path', trim($email));
    }
	
	public function setFromEmail($email)
    {
    	return $this->setData('from_email', trim($email));
    }
    
	public function setFromName($name)
    {
    	return $this->setData('from_name', trim($name));
    }
    
    public function setReplyTo($email)
    {
    	return $this->setData('reply_to', trim($email));
    }
    
    public function addAttachments(array $parts) {
    	if (empty($parts) === false) {
    		$this->addParts($parts);
    		$this->getMail()->setHasAttachments(true);
    	}
    	return $this;
    }
    
	public function addParts(array $parts) {
    	foreach ($parts as $part) {
    		$this->getMail()->addPart($part);
    	}
    	return $this;
    }
    
    public function reset()
    {
    	$this->mail = null;
    	$this->setData(array());
    	return $this;
    }
}