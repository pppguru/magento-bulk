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
/*
 * Display all suppliers for one product
 */
class MDN_Purchase_Block_Widget_Column_Renderer_ProductSuppliersSku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $productId = $row->getData($this->getColumn()->getIndex());
        $collection = mage::getModel('Purchase/ProductSupplier')
                ->getCollection()
                ->addFieldToFilter('pps_product_id', $productId)
                ->join('Purchase/Supplier', 'pps_supplier_num=sup_id');

        $suppliers = array();
        foreach ($collection as $supplier) {
            $buffer = $supplier->getpps_reference();
            if(strlen($supplier->getpps_product_name())>0){
                $buffer .= ' - '.$supplier->getpps_product_name();
            }
            $suppliers[] = $buffer;
        }

        return implode('<br>', $suppliers);
    }
    
}