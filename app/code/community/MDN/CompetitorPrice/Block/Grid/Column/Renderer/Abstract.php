<?php

abstract class MDN_CompetitorPrice_Block_Grid_Column_Renderer_Abstract extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {

        $productId = $row->getData($this->getColumn()->getIndex());
        $channel = $this->getChannel();
        $fieldName = $this->getFieldName();
        $value = $this->getValue($productId, $channel, $fieldName);

        if (!$channel || !Mage::helper('CompetitorPrice')->isConfigured()) {
            return 'Disabled';
        }

        $html = '';
        $html .= '<div id="competitor_price_'.$productId.'" class="competitor_price"></div>';
        $html .= "<script>competitorPriceObj.addProduct('".$productId."', '".$channel."', '".$fieldName."', '".$value."');</script>";

        return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        $productId = $row->getData($this->getColumn()->getIndex());
        $channel = $this->getChannel();

        $item = Mage::getModel('CompetitorPrice/Product')->loadByProductChannel($productId, $channel);
        if ($item->getId())
        {
            return $item->getOffersAsText();
        }
    }

    public abstract function getChannel();
    public abstract function getFieldName();
    public abstract function getValue($id, $channel, $fieldName);

}