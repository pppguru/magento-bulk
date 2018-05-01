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
class MDN_AdvancedStock_Block_Warehouse_Edit_Tabs_Assignment extends Mage_Adminhtml_Block_Widget_Form
{
	private $_stock = null;
	private $_websites = null;
		
	public function __construct()
	{
		$this->_blockGroup = 'AdvancedStock';
        $this->_objectId = 'id';
        $this->_controller = 'Warehouse';
		
		parent::__construct();
				
		$this->setTemplate('AdvancedStock/Warehouse/Edit/Tab/Assignment.phtml');
	}

	/**
	 * return current stock object
	 *
	 * @return unknown
	 */
	public function getStock()
	{
		if ($this->_stock == null)
		{
			$stockId = $this->getRequest()->getParam('stock_id');
			$this->_stock = mage::getModel('AdvancedStock/Warehouse')->load($stockId);
		}
		return $this->_stock;
	}

	
	/**
	 * Return assignments list
	 *
	 * @return unknown
	 */
	public function getAssignments()
	{
		return mage::getModel('AdvancedStock/Assignment')->getAssignments();
	}
	
	/**
	 * return websites list
	 *
	 * @return unknown
	 */
	public function getWebsites()
	{
		if ($this->_websites == null)
			$this->_websites = mage::getResourceModel('core/website_collection')->setLoadDefault(true);
		return $this->_websites;
	}
	
	/**
	 * Return radio button to assign stock to website
	 *
	 * @param unknown_type $website
	 * @param unknown_type $assignment
	 * @return unknown
	 */
	public function getWebsiteAssigmentCheckbox($website, $assignment)
	{

		$id = 'assignment['.$website->getId().']['.$assignment.']';
		$selected = '';
		if ($this->getStock()->isAssigned($website, $assignment))
		{
			$selected = ' checked="checked" ';
		}
		$retour = '<input type="checkbox" id="'.$id.'" name="'.$id.'" value="1" '.$selected.'>';
		
		return $retour;
	}

	
}