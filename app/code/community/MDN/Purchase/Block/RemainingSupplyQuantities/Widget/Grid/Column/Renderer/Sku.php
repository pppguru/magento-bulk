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
class MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Renderer_Sku
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	public function render(Varien_Object $row)
    {
		$url = $this->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $row->getId()));
    	return '<a href="'.$url.'" target="_blank">'.$row->getsku().'</a>';
    }
    
    public function renderExport(Varien_Object $row)
    {
    	return $row->getsku();
    }
}