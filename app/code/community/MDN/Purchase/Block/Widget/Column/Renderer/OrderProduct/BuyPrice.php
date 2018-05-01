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

class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_BuyPrice
	extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract 
{
    public function render(Varien_Object $item)
    {
		$name = 'pop_price_ht_' . $item->getId();

		$value = $item->getpop_price_ht();
		$html = '<input size="6" type="text" id="'.$name.'" name="'.$name.'" value="' . $item->getpop_price_ht() . '" onkeyup="updateOrderProductInformation(' . $item->getId() . ');" '.$this->getOnChange($item, $value).'><br>';
		$html .= '<div id="div_price_with_extended_cost_' . $item->getId() . '"></div>';

		$weightName = 'pop_weight_' . $item->getId();
		$html .= '<input size="6" type="hidden" id="'.$weightName.'" name="'.$weightName.'"
		value="' . $item->getpop_weight() . '">';


		return $html;
    }
	
	function getFieldName($row)
	{
		return 'pop_price_ht_' . $row->getId();
	}

    
}