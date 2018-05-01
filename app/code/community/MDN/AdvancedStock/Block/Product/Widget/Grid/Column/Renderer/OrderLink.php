<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_OrderLink
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		$html = '';
        $orderId = $row->getId();
        $orderDisplayId = $row->getincrement_id();

        if($orderId && $orderDisplayId){

              $urlInfo = array('url' => 'adminhtml/sales_order/view', 'param' => array('order_id' => $orderId));
              $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
              $html .= '<a href="' . $url . '" target="_blank">#' . $orderDisplayId . '</a>';
        }

		return $html;
    }

    public function renderExport(Varien_Object $row)
    {
        return  $row->getincrement_id();
    }

}

