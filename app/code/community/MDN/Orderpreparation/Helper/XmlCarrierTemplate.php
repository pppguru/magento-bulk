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
class MDN_Orderpreparation_Helper_XmlCarrierTemplate extends Mage_Core_Helper_Abstract
{
	/**
	 * Create xml output for carrier template
	 *
	 * @param unknown_type $carrierTemplate
	 */
	public function export($carrierTemplate)
	{
		$xml = '<?xml version="1.0" encoding="ISO-8859-1" ?>';
		$xml .= '<template>';

		$xml .= '<information>';
		foreach ($carrierTemplate->getData() as $key => $value)
		{
			$xml .= '<data name="'.$key.'" value="'.$value.'" />';
		}
		$xml .= '</information>';
		
		$xml .= '<fields>';
		foreach ($carrierTemplate->getFields() as $field)
		{
			$xml .= '<field>';			
			foreach ($field->getData() as $key => $value)
			{
				$xml .= '<data name="'.$key.'" value="'.$value.'" />';
			}
			$xml .= '</field>';			
		}
		$xml .= '</fields>';	
		
		$xml .= '</template>';
		
		return $xml;
	}
	
	/**
	 * Create a new template from a xml file
	 *
	 * @param unknown_type $xmlPath
	 */
	public function import($xmlPath)
	{
		//load document
		$domDocument = new DOMDocument();
		$domDocument->encoding = 'UTF-8'; 
		$domDocument->load($xmlPath);
		
		//test for errors
		if ((!$domDocument) || (!$domDocument->documentElement))
			throw new Exception('Unable to load xml content');
		
		//create template
		$template = mage::getModel('Orderpreparation/CarrierTemplate');
		foreach($domDocument->documentElement->getElementsByTagName('*') as $node)
		{
			if ($node->tagName == 'information')
			{
				foreach ($node->getElementsByTagName('data') as $nodeInfo)
				{
					$dataName = $nodeInfo->getAttribute('name');
					$dataValue = $nodeInfo->getAttribute('value');
					if ($dataName != 'ct_id')
						$template->setData($dataName, $dataValue);
				}
			}
		}
		$template->save();
		
		//create fields
		foreach($domDocument->documentElement->getElementsByTagName('fields') as $node)
		{
			foreach ($node->getElementsByTagName('field') as $nodeField)
			{
				$field = mage::getModel('Orderpreparation/CarrierTemplateField');
				foreach ($nodeField->getElementsByTagName('data') as $nodeData)
				{
					$dataName = $nodeData->getAttribute('name');
					$dataValue = $nodeData->getAttribute('value');
					if ($dataName != 'ctf_id')
						$field->setData($dataName, $dataValue);
				}				
				$field->setctf_template_id($template->getId());
				$field->save();
			}
		}

		return $template;
	}
	
}