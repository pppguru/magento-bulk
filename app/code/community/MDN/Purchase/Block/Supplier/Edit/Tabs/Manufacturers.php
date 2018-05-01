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
class MDN_Purchase_Block_Supplier_Edit_Tabs_Manufacturers extends Mage_Adminhtml_Block_Widget_Form
{
	private $_supplier_id;
	
	/**
	 * Constructeur
	 *
	 */
	public function __construct()
	{
		parent::__construct();
				
		$this->setTemplate('Purchase/Supplier/Edit/Tab/Manufacturers.phtml');
	}
	
	/**
	 * Définit le fournisseur
	 *
	 * @param unknown_type $value
	 */
	public function setSupplierId($value)
	{
		$this->_supplier_id = $value;
		return $this;
	}
		
	/**
	 * Retourne le fournisseur
	 *
	 * @param unknown_type $value
	 */
	public function getSupplierId()
	{
		return $this->_supplier_id;
	}
	
	/**
	 * Retourne la liste des fabricants associés
	 *
	 */
	public function getManufacturers()
	{
		$collection = Mage::GetModel('Purchase/ManufacturerSupplier')
			->getCollection()
			->addFieldToFilter('pms_supplier_id', $this->_supplier_id)
            ->join('Purchase/Manufacturer','man_id=pms_manufacturer_id');
			
		return  $collection;
	}
	
	/**
	 * Retourne un combo avec les manufactures pas encore associés
	 *
	 */
	public function getOtherManufacturesAsCombo($name)
	{
		//cree un tableau avec les id des manufacturers déja géré
		$collection = Mage::GetModel('Purchase/ManufacturerSupplier')
			->getCollection()
			->addFieldToFilter('pms_supplier_id', $this->_supplier_id);
		$t_ids = array();
		$t_ids[] = '-1';
		foreach ($collection as $item)
		{
			$t_ids[] = $item->getpms_manufacturer_id();
		}
		
		//Charge les manufacturers pas gérés
		$collection = Mage::GetModel('Purchase/Manufacturer')
			->getCollection()
			->addFieldToFilter('man_id', array('nin' => $t_ids));
            ;
		
        $retour = '<select id="'.$name.'"  name="'.$name.'">';
        $retour .= '<option value=""></option>';
        foreach ($collection as $item)
        {
        	$retour .= '<option value="'.$item->getman_id().'">'.$item->getman_name().'</option>';
        }
        $retour .= '</select>';
		return  $retour;
	}
	
	/**
	 * Retourne un menu déroulant avec les positionnements de prix possibles
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function getPricePositionAsCombo($name, $value)
	{
		$retour = '<select  id="'.$name.'" name="'.$name.'">';
		$values = explode(';', ';Bad;Average;Good;Excellent');	
		foreach($values as $item)
		{
			if ($item == $value)
				$selected = ' selected ';
			else 
				$selected = '';
			$retour .= '<option value="'.$item.'" '.$selected.'>'.$this->__($item).'</option>';		
		}
		$retour .= '</select>';
		return $retour;
	}
}