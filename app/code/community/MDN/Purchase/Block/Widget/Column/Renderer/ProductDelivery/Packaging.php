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

class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Packaging
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		$html = '';
		if ($row->getpop_packaging_value() > 1)
		{
			if ($row->getpop_packaging_name())
				$html = $row->getpop_packaging_name().'<br>';
			$html .= '<i>('.$this->__('%s units per pack', $row->getpop_packaging_value()).')</i>';
		}
		else
			$html = '-';
		return $html;
    }
    
}