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
class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_StockLocation
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $key = 'shelf_location_'.$row->getitem_id();
        $value = $row->getshelf_location();
        $filedSize = "30";
        $onChange = 'onchange="persistantGrid.logChange(this.name, \''.$row->getshelf_location().'\')"';
        return '<input type="text" name="'.$key.'" id="'.$key.'"  value="'.$value.'" size="'.$filedSize.'" '.$onChange.'>';
    }
}