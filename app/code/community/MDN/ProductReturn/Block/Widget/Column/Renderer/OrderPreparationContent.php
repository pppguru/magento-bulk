<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_OrderPreparationContent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //init vars
        $order = $row;
        $html  = '';
        foreach ($order->getAllItems() as $item) {
            $color = $this->getColor($item);
            $qty   = (int)$item->getRemainToShipQty();
            $html .= $this->format($item);
        }

        return $html;
    }

    /**
     * Define color for item depending of reservation, shipped ...
     *
     * @param <type> $orderItem
     *
     * @return string
     */
    public function format($orderItem)
    {
        $productId     = $orderItem->getproduct_id();
        $remaining_qty = $orderItem->getRemainToShipQty();

        //if all is shipped
        if ($orderItem->getRemainToShipQty() == 0)
            return '<font color="#000000">' . $orderItem->getName() . '</font>';

        //if doesn't manage stocks
        $productStockManagement = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        if (!$productStockManagement->getManageStock()) {
            return '<i>' . $orderItem->getName() . '</i>';
        } else {
            if ($orderItem->getreserved_qty() >= $remaining_qty) {
                return '<font color="green">' . $orderItem->getName() . '</font>';
            } else {
                return '<font color="#ff0000">' . $orderItem->getName() . '</font>';
            }
        }
    }

}
