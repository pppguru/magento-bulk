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
class MDN_Purchase_Block_Contact_SubGrid extends Mage_Adminhtml_Block_Widget_Form
{
	private $_EntityId = null;
	private $_EntityType = null;
	
	/**
	 * Constructeur
	 *
	 */
	public function __construct()
	{
		parent::__construct();	
	}
	
	/**
	 * Retourne le type de contact géré
	 *
	 */
	public function getEntityType()
	{
		return $this->_EntityType;
	}
	
	/**
	 * Retourne l'id de l'entite gérée
	 *
	 */
	public function getEntityId()
	{
		return $this->_EntityId;
	}
	
	/**
	 * Retourne la liste des contacts
	 *
	 */
	public function getContacts()
	{
		$collection = mage::getModel('Purchase/Contact')
			->getCollection()
			->addFieldToFilter('pc_type', $this->_EntityType)
			->addFieldToFilter('pc_entity_id', $this->_EntityId);

		return $collection;
	}
	
	/**
	 * Définit le type d'entité
	 *
	 * @param unknown_type $value
	 */
	public function setEntityType($value)
	{
		$this->_EntityType = $value;
		return $this;
	}

	/**
	 * Définit la valeur de l'entité
	 *
	 * @param unknown_type $value
	 */
	public function setEntityId($value)
	{
		$this->_EntityId = $value;
		return $this;
	}

}