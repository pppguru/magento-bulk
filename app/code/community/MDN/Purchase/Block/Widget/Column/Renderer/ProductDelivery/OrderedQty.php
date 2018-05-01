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

class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_OrderedQty
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
	
		$html = '';
		if ($row->getpop_packaging_value() > 1)
		{
			$packCount = $row->getOrderedQty();
			$html = $this->__('%s packs', $packCount);
			$html .= '<br><i>('.$this->__('%s units', $row->getpop_qty()).')</i>';
		}
		else
			$html = $row->getpop_qty();
		return $html;
    }
    
}