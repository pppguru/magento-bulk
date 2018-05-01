<?php

class MDN_CompetitorPrice_Block_Grid_Column_Renderer_Ean extends MDN_CompetitorPrice_Block_Grid_Column_Renderer_Abstract
{

    public function getValue($id, $channel, $mode)
    {
        return Mage::helper('CompetitorPrice')->getEan($id);
    }

    public function getChannel()
    {
        return Mage::helper('CompetitorPrice')->getGoogleShoppingChannel();
    }

    public function getFieldName()
    {
        return MDN_CompetitorPrice_Helper_Data::kModeUpcEan;
    }

}