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
class MDN_Purchase_Model_Contact  extends Mage_Core_Model_Abstract
{
	
	/*****************************************************************************************************************************
	* ***************************************************************************************************************************
	* Constructeur
	*
	*/
	public function _construct()
	{
		parent::_construct();
		$this->_init('Purchase/Contact');
	}
		
	/**
	 * Retourne l'entite liee au contact (manufacturer ou supplier)
	 *
	 */
	public function getLinkedEntity()
	{
		$entity = null;
		switch ($this->getpc_type())
		{
			case 'manufacturer':
				$entity = mage::getModel('Purchase/Manufacturer')->load($this->getpc_entity_id());
				break;
			case 'supplier':
				$entity = mage::getModel('Purchase/Supplier')->load($this->getpc_entity_id());
				break;
		}
		
		return $entity;
	}
	
		
	/**
	 * Retourne l'entite sous forme de texte
	 *
	 */
	public function getContactEntityAsText()
	{		
		$text = '';
		$entity = $this->getLinkedEntity();

        if($entity){
            switch ($this->getpc_type())
            {
                case 'manufacturer':
                    $text .= '<a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/Purchase_Manufacturers/Edit/').'man_id/'.$entity->getman_id().'" target="_blank">';
                    $text .= Mage::helper('purchase')->__('Manufacturer').' - '.$entity->getman_name();
                    break;
                case 'supplier':
                    $text .= '<a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/Purchase_Suppliers/Edit/').'sup_id/'.$entity->getsup_id().'" target="_blank">';
                    $text .= Mage::helper('purchase')->__('Supplier').' - '.$entity->getsup_name();
                    break;
            }
            $text .= '</a>';
        }
		return $text;
	}
	
}