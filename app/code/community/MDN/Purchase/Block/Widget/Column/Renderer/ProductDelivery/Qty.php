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

class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Qty
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		$rowId = $row->getId();
        $name = 'delivery_qty_'.$rowId;
		$value = '0';
		$onChange = 'onchange="persistantDeliveryGrid.logChange(this.name, \''.$value.'\');notifyIfQuantityIsHigherThanExpected(this.value,'.$rowId.');"';
		$html = '<input type="text" name="'.$name.'" id="'.$name.'" value="0" size="3" '.$onChange.'>';
		
		return $html;
    }
    
}