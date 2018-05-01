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
class MDN_Purchase_Block_Contact_Edit extends Mage_Adminhtml_Block_Widget_Form
{
	
	private $_contact = null;
	
	/**
	 * Constructeur: on charge
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		
		//charge le manufacturer
        $pc_num = Mage::app()->getRequest()->getParam('pc_num', false);	
        $model = Mage::getModel('Purchase/Contact');
		$this->_contact = $model->load($pc_num);
		
	}
	
	/**
	 * Retourne l'url pour retourner a la liste
	 */
	public function GetBackUrl()
	{
		return $this->getUrl('adminhtml/Purchase_Contacts/List', array());
	}

	/**
	 * Retourne un combo avec la liste des pays
	 *
	 * @param unknown_type $name
	 * @return unknown
	 */
	public function getCountryCombo($name, $value)
	{
		$retour = '<select id="'.$name.'" name="'.$name.'">';
		$retour .= '<option value=""></option>';

		//charge la liste des pays
		$collection = Mage::getModel('directory/country')->getCollection()->toOptionArray();
		foreach ($collection as $item)
		{
			if ($item['value'] == $value)
				$selected = ' selected ';
			else 
				$selected = '';
			$retour .= '<option value="'.$item['value'].'" '.$selected.'>'.$item['label'].'</option>';
		}
		
		$retour .= '</select>';
		return $retour;
	}
	
	/**
	 * Retourne l'objet
	 *
	 * @return unknown
	 */
	public function getContact()
	{
		return $this->_contact;
	}

}
