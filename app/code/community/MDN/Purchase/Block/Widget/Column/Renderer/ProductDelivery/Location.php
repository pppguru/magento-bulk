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
class MDN_Purchase_Block_Widget_Column_Renderer_ProductDelivery_Location extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    private static $_warehouse;
    
    public function render(Varien_Object $row) {
        $html = '';
        $name = 'delivery_location_' . $row->getId();
        $value = '';
        
        $onChange = 'onchange="persistantDeliveryGrid.logChange(this.name, \'' . $value . '\')"';
        $currentLocation = $this->getWarehouse()->getProductLocation($row->getpop_product_id());
        if ($currentLocation)
            $html .= $currentLocation.'<br>';
        $html .= '<input type="text" id="' . $name . '" name="' . $name . '" value="" size="5" ' . $onChange . '>';
        return $html;
    }

    /**
     * Get main warehouse (to retrieve location)
     * @return type 
     */
    protected function getWarehouse()
    {
        if (self::$_warehouse == null)
        {
            self::$_warehouse = Mage::getModel('AdvancedStock/Warehouse')->load(1);
        }
        return self::$_warehouse;
    }
    

}