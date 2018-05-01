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
class MDN_Purchase_Block_Manufacturer_New extends Mage_Adminhtml_Block_Widget_Form
{
		
	/**
	 * Constructeur: on charge le devis
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		
	}
	
	/**
	 * Retourne l'url pour retourner a la fiche client
	 */
	public function GetBackUrl()
	{
		return $this->getUrl('adminhtml/Purchase_Manufacturers/List', array());
	}

	/**
	 * Retourne un combo avec la liste des pays
	 *
	 * @param unknown_type $name
	 * @return unknown
	 */
	public function getCountryCombo($name)
	{
		$retour = '<select id="'.$name.'" name="'.$name.'">';
		$retour .= '<option value=""></option>';

		//charge la liste des pays
		$collection = Mage::getModel('directory/country')->getCollection()->toOptionArray();
		foreach ($collection as $item)
		{
			$retour .= '<option value="'.$item['value'].'">'.$item['label'].'</option>';
		}
		
		$retour .= '</select>';
		return $retour;
	}
}
