<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_Picture extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $imageUrl = Mage::helper('AdvancedStock/Product_Image')->getProductImageUrl($row->getId());
        if ($imageUrl) {
            $url = $this->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $row->getentity_id()));
            $html = '<a href="' . $url . '"><img src="' . $imageUrl . '" width="50" height="50"></a>';
            return $html;
        }
    }

}