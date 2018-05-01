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
class MDN_Purchase_Block_Manufacturer_Edit extends Mage_Adminhtml_Block_Widget_Form
{
	
	private $_manufacturer = null;
	
	/**
	 * Constructeur: on charge le devis
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		
		//charge le manufacturer
        $man_id = Mage::app()->getRequest()->getParam('man_id', false);	
        $model = Mage::getModel('Purchase/Manufacturer');
		$this->_manufacturer = $model->load($man_id);
		
	}
	
	/**
	 * Retourne l'url pour retourner a la liste des manufacturers
	 */
	public function GetBackUrl()
	{
		return $this->getUrl('adminhtml/Purchase_Manufacturers/List', array());
	}
	
	public function getDeleteUrl()
	{
		return $this->getUrl('adminhtml/Purchase_Manufacturers/Delete', array('man_id' => $this->getManufacturer()->getId()));
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
	 * Retourne l'objet manufacturer
	 *
	 * @return unknown
	 */
	public function getManufacturer()
	{
		return $this->_manufacturer;
	}
	
	/**
	 * Retourne la liste des contacts du fabricant
	 *
	 */
	public function getContacts()
	{
		$collection = mage::getModel('Purchase/Contact')
			->getCollection()
			->addFieldToFilter('pc_type', 'manufacturer')
			->addFieldToFilter('pc_entity_id', $this->getManufacturer()->getId());

		return $collection;
	}
	
	/**
	 * Retourne la liste des produits liés au fabricant
	 *
	 */
	public function getProducts()
	{
		$collection = mage::GetModel('Purchase/ProductManufacturer')
				->getCollection()
	            ->join('Catalog/Product',
			           'ppm_product_id=entity_id')
				->addFieldToFilter('ppm_manufacturer_num', $this->getManufacturer()->getId())
				;
				
		return $collection;
	}
	
	/**
	 * Retourne la liste des manufacturer magento sous la forme d'un menu deroulant
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function getMagentoManufacturerListAsCombo($name, $value)
	{
		$retour = '<select id="'.$name.'" name="'.$name.'">';
		
        $manufacturerCode = mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeCode();

        if($manufacturerCode){
          $product = Mage::getModel('catalog/product');
          $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
              ->setEntityTypeFilter($product->getResource()->getTypeId())
              ->addFieldToFilter('attribute_code', $manufacturerCode)
              ->load(false);

          $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
          $manufacturers = $attribute->getSource()->getAllOptions(false);

          foreach($manufacturers as $manufacturer)
          {
              if ($manufacturer['value'] == $value)
                  $selected = ' selected ';
              else
                  $selected = '';
              $retour .= '<option value="'.$manufacturer['value'].'" '.$selected.'>'.$manufacturer['label'].'</option>';
          }
        }

		$retour .= '</select>';
    	return $retour;	
	}
	
}
