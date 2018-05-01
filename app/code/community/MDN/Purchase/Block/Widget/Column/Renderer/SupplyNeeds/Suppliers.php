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
class MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeeds_Suppliers
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        return $this->getSuppliers($row,true);    }

    public function renderExport(Varien_Object $row) {
        return $this->getSuppliers($row,false);
    }

    public function getSuppliers($row,$useHtml = true) {

        $suppliers = Mage::getModel('Purchase/ProductSupplier')->getSuppliersForProduct($row);

        $buffer = '';
        foreach($suppliers as $supplier)
        {
            $buffer .= $supplier->getsup_name();

            if($supplier->getpps_last_unit_price()>0) {
                if($useHtml)
                    $buffer .= ' : '.Mage::helper('core')->currency($supplier->getpps_last_unit_price());
                else
                    $buffer .= ' : '.$supplier->getpps_last_unit_price();
            }

            if($supplier->getpps_quantity_product()>0) {
                if($useHtml)
                    $buffer .= '<i>';

                $buffer .='  ('.(int)$supplier->getpps_quantity_product().')';

                if($useHtml)
                    $buffer .= '</i>';
            }

            $supplyDelay = ($supplier->getpps_supply_delay())?$supplier->getpps_supply_delay():$supplier->getsup_supply_delay();
            if($supplyDelay) {
                $buffer .= ' - ' . (int)$supplyDelay . ' '.$this->__('days');
            }

            if($useHtml)
                $buffer .= '<br>';
        }

        return $buffer;
    }


}