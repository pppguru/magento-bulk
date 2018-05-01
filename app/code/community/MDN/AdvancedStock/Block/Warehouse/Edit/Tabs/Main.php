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
class MDN_AdvancedStock_Block_Warehouse_Edit_Tabs_Main extends Mage_Adminhtml_Block_Widget_Form
{
	private $_stock = null;
		
	public function __construct()
	{
		$this->_blockGroup = 'AdvancedStock';
        $this->_objectId = 'id';
        $this->_controller = 'Warehouse';
		
		parent::__construct();
				
		$this->setTemplate('AdvancedStock/Warehouse/Edit/Tab/Main.phtml');
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
            if(isset($stockId) && $stockId>0) {
                $this->_stock = mage::getModel('AdvancedStock/Warehouse')->load($stockId);
            }
		}
		return $this->_stock;
	}

    public function getYesNoCombo($name, $currentValue) {
        $values = array('0' => $this->__('No'), '1' => $this->__('Yes'));
        $html = '<select name="' . $name . '" id="' . $name . '">';
        foreach ($values as $key => $label) {
            $selected = '';
            if ($key == $currentValue)
                $selected = ' selected ';
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

	
}
