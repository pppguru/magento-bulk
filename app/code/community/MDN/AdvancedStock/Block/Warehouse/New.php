<?php

/**
 * Customer edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_AdvancedStock_Block_Warehouse_New extends Mage_Adminhtml_Block_Widget_Form
{
	public function getSubmitUrl()
	{
		return $this->getUrl('adminhtml/AdvancedStock_Warehouse/Create'); 
	}
}