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
class MDN_AdvancedStock_Block_Warehouse_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
	 * Load object
	 *
	 */
	public function __construct()
	{
        $this->_objectId = 'id';
        $this->_controller = 'Warehouse';
        $this->_blockGroup = 'AdvancedStock';
		
		parent::__construct();

        
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/warehouse_delete')) {
          $stock_id = $this->getRequest()->getParam('stock_id');
          $this->_addButton(
            'print', array(
                  'label' => Mage::helper('AdvancedStock')->__('Delete'),
                  'onclick' => "window.location.href='" . $this->getDeleteWarehouseUrl($stock_id) . "'",
                  'level' => -1,
                  'class' => 'delete'
                  )
          );
        }
	}
    
	/**
	 * Page head title
	 *
	 * @return unknown
	 */
	public function getHeaderText()
    {
        return $this->__('Edit warehouse');
    }
	
	/**
	 * Return url to submit form
	 *
	 * @return unknown
	 */
	public function getSaveUrl()
	{
		return $this->getUrl('adminhtml/AdvancedStock_Warehouse/Save');
	}

    /**
	 * Return url to delete a warehouse
	 *
	 * @return unknown
	 */
	public function getDeleteWarehouseUrl($stock_id)
	{
       return $this->getUrl('adminhtml/AdvancedStock_Warehouse/Delete', array('stock_id' => $stock_id));
	}

    /**
	 * Return url to warehouse grid
	 *
	 * @return unknown
	 */
	public function getBackUrl()
	{
		return $this->getUrl('adminhtml/AdvancedStock_Warehouse/Grid');
	}
	
}
