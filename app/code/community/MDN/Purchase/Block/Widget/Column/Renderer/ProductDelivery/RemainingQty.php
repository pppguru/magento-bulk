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

class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_RemainingQty
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $name = 'remaining_qty_'.$row->getId();
        $remaining = ($row->getpop_qty() - $row->getpop_supplied_qty());
        $hiddenQtyForCheck = $remaining;
		if ($row->getpop_packaging_value() > 1)
		{
			$remainingPackCount = $row->getRemainingQty();
			$html .= $this->__('%s packs', $remainingPackCount);
			$html .= '<br><i>('.$this->__('%s units', $remaining).')</i>';
            //$hiddenQtyForCheck = $remainingPackCount; //not sure
		}
		else
			$html .= $remaining;

        $html .= '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$hiddenQtyForCheck.'" >';
		return $html;
	
    }
    
}