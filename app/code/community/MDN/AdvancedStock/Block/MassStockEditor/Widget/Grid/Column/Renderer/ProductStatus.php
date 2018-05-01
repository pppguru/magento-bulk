<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_ProductStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $html = '';

        $pid = $row->getproduct_id();
        $p = mage::getModel('catalog/product')->load($pid);
        if($p->getId()>0){
            if($p->getstatus() == 1){
                $html = Mage::helper('AdvancedStock')->__('Enabled');
            }else{
                $html = Mage::helper('AdvancedStock')->__('Disabled');
            }
        }
        
        return $html;
    }

}