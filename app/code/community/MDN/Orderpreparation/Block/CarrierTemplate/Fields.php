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
class MDN_Orderpreparation_Block_CarrierTemplate_Fields extends Mage_Adminhtml_Block_Widget_Form
{
	protected $_TemplateId = null;
	protected $_Type = null;
	
	protected $_usablesCodes = null;

	/**
	 * Init functions
	 *
	 * @param unknown_type $templateID
	 */
	public function setTemplateId($templateID)
	{
		$this->_TemplateId = $templateID;
		return $this;
	}
	public function setType($type)
	{
		$this->_Type = $type;
		return $this;
	}
	
	/**
	 * Return fields collection
	 *
	 */
	public function getFields()
	{
		$carrierTemplate = mage::getModel('Orderpreparation/CarrierTemplate')->load($this->_TemplateId);
		return $carrierTemplate->getFields($this->_Type);
	}
	
	/**
	 * Return formats as combobox
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function getFormatCombo($name, $currentValue)
	{
		$formats = mage::helper('Orderpreparation/FieldFormat')->getFieldFormaters();
		
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$retour .= '<option value=""></option>';
		foreach($formats as $key => $value)
		{
			$selected = '';
			if ($key == $currentValue)
				$selected = ' selected ';
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';			
		}
		$retour .= '</select>';
		return $retour;
	}
	
	public function getImportContentAsCombo($name, $currentValue)
	{
		$formats = mage::helper('Orderpreparation/FieldFormat')->getImportContents();
		
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$retour .= '<option value=""></option>';
		foreach($formats as $key => $value)
		{
			$selected = '';
			if ($key == $currentValue)
				$selected = ' selected ';
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';			
		}
		$retour .= '</select>';
		return $retour;
	}
	
	
    /**
     * return all usable codes in content field
     *
     * @return unknown
     */
    public function getUsableCodesInstructions()
    {
    	if ($this->_usablesCodes == null)
    	{
    		$carrierTemplate = mage::getModel('Orderpreparation/CarrierTemplate')->load($this->_TemplateId);
	    	$this->_usablesCodes = $this->__('You can use the following codes :')."<br>";
	    	foreach($carrierTemplate->getUsableCodes() as $code)
	    	{
	    		$this->_usablesCodes .= ' {'.$code.'} ';
	    	}
    	}
    	return $this->_usablesCodes;
    }
}