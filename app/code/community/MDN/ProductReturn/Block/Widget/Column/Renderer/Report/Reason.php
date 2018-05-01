<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_Report_Reason extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {

        //get data
        if ($this->getColumn()->getuse_product_id())
            $productId = $row->getrp_product_id();
        else
            $productId = null;
        $from   = $row->getrrp_from();
        $to     = $row->getrrp_to();
        $reason = $this->getColumn()->getreason();

        //return value
        return Mage::helper('ProductReturn/Report')->getReasonCount($productId, $from, $to, $reason);
    }

}
