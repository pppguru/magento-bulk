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

class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Textbox
	extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract 
{
    public function render(Varien_Object $row)
    {
		$textboxSize = $this->getColumn()->gettextbox_size();
		$textboxName = $this->getFieldName($row);
		$value = $row[$this->getColumn()->getindex()];
	
		$onkeyup = $this->getColumn()->getonkeyup();
		$onkeyup = ' onkeyup="'.$onkeyup.'" ';
		$onkeyup = str_replace('{id}', $row->getpop_num(), $onkeyup);
	
		$html = '<input '.$onkeyup.' '.$this->getOnChange($row, $value).' type="text" name="'.$textboxName.'" id="'.$textboxName.'" size="'.$textboxSize.'" value="'.$value.'">';
		$html .= $this->getColumn()->getpostfix();
		
		return $html;
    }
	
	public function getFieldName(Varien_Object $row)
	{
		$textboxName = $this->getColumn()->gettextbox_name();
		$textboxName = str_replace('{id}', $row->getpop_num(), $textboxName);
		return $textboxName;
	}
    
}