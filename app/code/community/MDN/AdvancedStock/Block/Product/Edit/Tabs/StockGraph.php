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
class MDN_AdvancedStock_Block_Product_Edit_Tabs_StockGraph extends Mage_Adminhtml_Block_Widget_Form
{
	private $_defaultStock = null;
	
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
		$this->_blockGroup = 'AdvancedStock';
        $this->_objectId = 'id';
        $this->_controller = 'product';
        
        
		parent::__construct();

	    $this->setTemplate('AdvancedStock/Product/Edit/Tab/StockGraph.phtml');
		
	}	
	
	public function getGroupByAsCombo($name)
	{
		$retour = '<select name="'.$name.'" id="'.$name.'">';

		$retour .= '<option value="day">'.$this->__('Day').'</option>';
		$retour .= '<option value="week">'.$this->__('Week').'</option>';
		$retour .= '<option value="month" selected>'.$this->__('Month').'</option>';
		$retour .= '<option value="year">'.$this->__('Year').'</option>';
		
		$retour .= '</select>';
		return $retour;
	}
	
}