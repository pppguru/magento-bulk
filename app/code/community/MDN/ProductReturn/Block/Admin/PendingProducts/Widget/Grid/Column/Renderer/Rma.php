<?php


class MDN_ProductReturn_Block_Admin_PendingProducts_Widget_Grid_Column_Renderer_Rma
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //init vars
        $url  = $this->getUrl('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $row->getrma_id()));
        $html = '<a href="' . $url . '">' . $row->getrma_ref() . '</a>';

        return $html;
    }
}
