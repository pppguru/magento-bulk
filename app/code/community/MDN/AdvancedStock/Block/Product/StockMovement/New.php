<?php

/**
 * Customer edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_AdvancedStock_Block_Product_StockMovement_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
	private $_productId = null;
	
    public function __construct() {
        $this->_controller = null;
        parent::__construct();
    }
	
	/**
	 * Commit url (create stock movement)
	 *
	 * @return unknown
	 */
	public function getCommitUrl()
	{
		return $this->getUrl('adminhtml/AdvancedStock_StockMovement/Create');
	}
	
	/**
	 * Validation url (to check if we can create stock movemnt
	 *
	 * @return unknown
	 */
	public function getValidationUrl()
	{
		return $this->getUrl('adminhtml/AdvancedStock_StockMovement/Validate');
	}

	/**
	 * Get / set for current product id
	 *
	 * @param unknown_type $productId
	 * @return unknown
	 */
	public function setProductId($productId)
	{
		$this->_productId = $productId;
		return $this;
	}
	public function getProductId()
	{
		return $this->_productId;
	}
	
	/**
	 * Return combo box with warehouses
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function getWarehousesAsCombo($name, $value = '')
	{
		$html = '<select name="'.$name.'" id="'.$name.'">';
        $html .= '<option value=""></option>';
		
		$collection = mage::getModel('AdvancedStock/Warehouse')->getVisibleWarehouses();
		foreach ($collection as $item)
		{
			$selected = '';
			if ($item->getId() == $value)
				$selected = ' selected ';
            $html .= '<option value="'.$item->getId().'" '.$selected.'>'.$item->getstock_name().'</option>';
		}

        $html .= '</select>';
		return $html;
	}
	
	/**
	 * Combo box for stock movement types
	 *
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function getTypesAsCombo($name, $value = '')
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';
		$retour .= '<option value=""></option>';
		
		$collection = mage::getModel('AdvancedStock/StockMovement')->GetTypes();
		foreach ($collection as $key => $label)
		{
			$selected = '';
			if ($key == $value)
				$selected = ' selected ';
			$retour .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
		}
		
		$retour .= '</select>';
		return $retour;
		
	}
}