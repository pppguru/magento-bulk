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
class MDN_Purchase_Block_Product_Edit_Tabs_AssociatedManufacturers extends Mage_Adminhtml_Block_Template
{
	/**
	 * Product get/set
	 *
	 * @var unknown_type
	 */
	private $_product = null;
	public function setProduct($Product)
	{
		$this->_product = $Product;
		return $this;
	}
	public function getProduct()
	{
		return $this->_product;
	}
		
	/**
	 * Constructeur
	 *
	 */
	public function __construct()
	{
		parent::__construct();
				
	}
	
	/**
	 * Retourne la liste des manufacturer associé à un produit
	 *
	 * @return unknown
	 */
	public function getManufacturers()
	{
		$collection = mage::GetModel('Purchase/ProductManufacturer')
						->getCollection()
			            ->join('Purchase/Manufacturer',
					           'man_id=ppm_manufacturer_num')
						->addFieldToFilter('ppm_product_id', $this->getProduct()->getId())
						;
		return $collection;
	}
			
	/**
	 * Retourne la liste des Fabricants non liés au produit sous la forme d'un combo
	 *
	 */
	public function getNonLinkedManufacturersAsCombo($name='manufacturer')
	{
		//recupere la liste des manufacturers liés
		$collection = mage::GetModel('Purchase/ProductManufacturer')
				->getCollection()
				->addFieldToFilter('ppm_product_id', $this->getProduct()->getId())
				;
		$t_ids = array();
		$t_ids[] = -1;
		foreach ($collection as $item)
		{
			$t_ids[] = $item->getppm_manufacturer_num();
		}
						
		//Recupere la liste
		$collection = mage::GetModel('Purchase/Manufacturer')
						->getCollection()
						->addFieldToFilter('man_id', array('nin' => $t_ids));
		
		//transforme en combo
		$retour = '<select id="'.$name.'" name="'.$name.'">';
		foreach($collection as $item)
		{
			$retour .= '<option value="'.$item->getId().'">'.$item->getman_name().'</option>';
		}
		$retour .= '</select>';
		
		//retour
		return $retour;
	}
}