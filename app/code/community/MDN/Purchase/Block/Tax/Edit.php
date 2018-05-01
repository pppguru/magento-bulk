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
class MDN_Purchase_Block_Tax_Edit extends Mage_Adminhtml_Block_Widget_Form
{
	private $_TaxRate = null;
	
	/**
	 * Constructeur
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		
		//Charge le tax rate
        $ptr_id = Mage::app()->getRequest()->getParam('ptr_id', false);	
        $model = Mage::getModel('Purchase/TaxRates');
		$this->_TaxRate = $model->load($ptr_id);
	}
	
	public function getTaxRate()
	{
		return $this->_TaxRate;
	}
	
	public function getBackUrl()
	{
		return $this->getUrl('adminhtml/Purchase_Tax/List');
	}
		
	public function getDeleteUrl()
	{
		return $this->getUrl('adminhtml/Purchase_Tax/Delete', array('ptr_id' => $this->getTaxRate()->getId()));
	}
	
}