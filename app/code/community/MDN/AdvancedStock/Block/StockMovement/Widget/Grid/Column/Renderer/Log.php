<?php

class MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_Log extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($row->getsm_type() == 'adjustment' || $row->getsm_type() == 'order')
        {
            $url = $this->getUrl('adminhtml/AdvancedStock_StockMovement/DownloadReason', array('sm_id' => $row->getId()));
            return '<a href="'.$url.'">'.$this->__('Download').'</a>';
        }
    }

}