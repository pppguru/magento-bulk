<?php

class MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsSummary
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $productId = $row->getId();

	$block = $this->getLayout()->createBlock('Purchase/Product_Widget_StockDetails_Summary');
	$block->setProduct($row);
	$block->setTemplate('Purchase/Product/StockDetails/Summary.phtml');
	$html = $block->toHtml();

        return $html;

    }

}