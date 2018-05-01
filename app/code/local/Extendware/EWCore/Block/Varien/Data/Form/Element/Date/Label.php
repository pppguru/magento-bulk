<?php

class Extendware_EWCore_Block_Varien_Data_Form_Element_Date_Label extends Varien_Data_Form_Element_Label {

    /**
     * Get date value as string.
     * Format can be specified, or it will be taken from $this->getFormat()
     *
     * @param string $format (compatible with Zend_Date)
     * @return string
     */
    public function getValue($format = null)
    {
		$data = $this->getData('value');
		if ($data === null) return $data;
		if ($format === null) $format = $this->getFormat();
		try {
			$data = Mage::app()->getLocale()->date($data, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
		} catch (Exception $e) {
			$data = Mage::app()->getLocale()->date($data, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
		}
		return $data;
    }


	public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);
        if (empty($value) === true) {
        	$value = $this->getDefault();
        }
        return $this->_escape($value);
    }
    
	public function getElementHtml()
    {
        $html = $this->getBold() ? '<strong>' : '';
        $html.= $this->getEscapedValue();
        $html.= $this->getBold() ? '</strong>' : '';
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}
	