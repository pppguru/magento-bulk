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

class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Serial
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		$name = 'delivery_serials_'.$row->getId();
		$value = '';
		$onChange = 'onchange="persistantDeliveryGrid.logChange(this.name, \''.$value.'\')"';
		$html = '<textarea '.$onChange.' onkeyup="displaySerialsCount('.$row->getId().');" name="'.$name.'" id="'.$name.'" cols="30" rows="3"></textarea>';
		$html .= '<br><span id="serials_count_'.$row->getId().'" name="serials_count_'.$row->getId().'"> 0x</span>';
		return $html;
    }
    
}