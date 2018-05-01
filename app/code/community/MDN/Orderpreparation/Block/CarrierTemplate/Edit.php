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
class MDN_Orderpreparation_Block_CarrierTemplate_Edit extends Mage_Adminhtml_Block_Widget_Form
{
	private $_carrierTemplate = null;
	
	/**
	 * return current carrier template object
	 *
	 * @return unknown
	 */
	public function getCarrierTemplate()
	{
		if ($this->_carrierTemplate == null)
		{
			$templateId = $this->getRequest()->getParam('ct_id');
			$this->_carrierTemplate = mage::getModel('Orderpreparation/CarrierTemplate')->load($templateId);
		}
		return $this->_carrierTemplate;
	}
	
	/**
	 * return a combobox with file formats
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function getFileFormatAsCombo($name, $currentValue)
	{
		$data = mage::helper('Orderpreparation/FieldFormat')->getFileFormats();
		
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		foreach($data as $key => $value)
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
	 * 
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function getFieldDelimiterAsCombo($name, $currentValue)
	{
		$data = mage::helper('Orderpreparation/FieldFormat')->getFieldDelimiters();
		
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		foreach($data as $key => $value)
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
	 * 
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function getFieldSeparatorAsCombo($name, $currentValue)
	{
		$data = mage::helper('Orderpreparation/FieldFormat')->getFieldSeparators();
		
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		foreach($data as $key => $value)
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
	 * Return a combobox with all shipping method
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function getShippingMethodAsCombo($name, $value)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$retour .= '<option value="">'.$this->__('None').'</option>';		
		$config = Mage::getStoreConfig('carriers');
        foreach ($config as $code => $methodConfig) {
        	$selected = '';
        	if ($code == $value)
        		$selected = ' selected ';
       		$retour .= '<option value="'.$code.'" '.$selected.'>'.(!empty($methodConfig['title']) ? $methodConfig['title'] : 'empty').'</option>';
        }
        
		$retour .= '</select>';
		return $retour;
	}
	
	public function getLineEndAsCombo($name, $currentValue)
	{
		$data = mage::helper('Orderpreparation/FieldFormat')->getLineEnds();
		
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		foreach($data as $key => $value)
		{
			$selected = '';
			if ($key == $currentValue)
				$selected = ' selected ';
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';			
		}
		$retour .= '</select>';
		return $retour;
	}
	
	public function getTypesAsCombo($name, $currentValue, $onchange = '')
	{
		$data = mage::helper('Orderpreparation/CarrierTemplate')->getTypes();
		
		$retour = '<select name="'.$name.'" id="'.$name.'" onchange="'.$onchange.'">';
		foreach($data as $key => $value)
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
     * Return url to create a new template
     *
     * @return unknown
     */
    public function getBackUrl()
    {
    	return $this->getUrl('adminhtml/OrderPreparation_CarrierTemplate/Grid');
    }
    
    /**
     * Return url to export template as xml file
     *
     */
    public function getExportTemplateUrl()
    {
    	return $this->getUrl('adminhtml/OrderPreparation_CarrierTemplate/Export', array('ct_id' => $this->getCarrierTemplate()->getId()));
    }
    
    public function getDeleteUrl()
    {
    	return $this->getUrl('adminhtml/OrderPreparation_CarrierTemplate/Delete', array('ct_id' => $this->getCarrierTemplate()->getId()));
    }


}