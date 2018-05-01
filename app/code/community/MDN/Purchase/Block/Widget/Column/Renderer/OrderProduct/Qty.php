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

class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Qty
	extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract 
{
    public function render(Varien_Object $row)
    {
		$html = '';
		
		//if packaging is not enabled
		if (!mage::helper('purchase/Product_Packaging')->isEnabled())
		{
			//standard input field
			$html = '<input onkeyup="updateOrderProductInformation('.$row->getId().')" onchange="persistantProductGrid.logChange(this.name, \''.$row->getpop_qty().'\')" type="text" name="pop_qty_'.$row->getId().'" id="pop_qty_'.$row->getId().'" size="3" value="'.$row->getpop_qty().'">';
		}
		else
		{
			//create one field to fill the packaging number
			$packagingCoef = $row->getpop_packaging_value();
			$packageCount = (int)($row->getpop_qty() / $packagingCoef);
			$name = 'package_count_'.$row->getId();
			$html .= '<input onkeyup="updateQtyFromPackageCount('.$row->getId().');" 
							 onchange="persistantProductGrid.logChange(this.name, \''.$packageCount.'\'); persistantProductGrid.logChange(\'pop_qty_'.$row->getId().'\', \''.$row->getpop_qty().'\');" 
							 type="text" 
							 id="'.$name.'" 
							 name="'.$name.'" 
							 value="'.$packageCount.'" 
							 size="3">';
			
			//add a hidden field that contains the total qty
			$html .= '<br><input onkeyup="updateOrderProductInformation('.$row->getId().')" 
								 onchange="persistantProductGrid.logChange(this.name, \''.$row->getpop_qty().'\')" 
								 type="text" 
								 style="display : none;"
								 name="pop_qty_'.$row->getId().'" 
								 id="pop_qty_'.$row->getId().'" 
								 size="3" 
								 value="'.$row->getpop_qty().'">';
		}
		
		return $html;
    }
    
}