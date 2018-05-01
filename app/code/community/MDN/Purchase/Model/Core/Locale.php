<?php

class MDN_Purchase_Model_Core_Locale extends Mage_Core_Model_Locale
{
	public function emulateLocale($localeId)
	{
            $this->_emulatedLocales[] = clone $this->getLocale();
            $this->_locale = new Zend_Locale($localeId);
            $this->_localeCode = $this->_locale->toString();
            Mage::getSingleton('core/translate')->setLocale($this->_locale)->init('frontend', true);
	}
}