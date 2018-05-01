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
class MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_Qty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $html = '';
        $value = $row->getstp_qty_requested();
        $name = 'stp_qty_requested_' . $row->getId();
        
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/stock_management/stock_transfer/edit_quantity'))
        {
            $html .= '<input onchange="persistantProductGrid.logChange(this.name, \'' . $value . '\');"
                             type="text"
                             id="' . $name . '"
                             name="' . $name . '"
                             value="' . $value . '"
                             size="3">';
        }
        else
        {
            $html .= '<input
                             type="hidden"
                             id="' . $name . '"
                             name="' . $name . '"
                             value="' . $value . '"
                             >'.$value;
        }
        
        return $html;
    }

}