<?php

class MDN_AdvancedStock_Model_Sales_Order_Margin {


    /**
     * get total margin
     *
     */
    public function getMargin($order)
    {
        $retour = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            $retour += $item->getMargin();
        }
        return $retour;
    }

    /**
     * get total margin in percent
     *
     */
    public function getMarginPercent($order)
    {
        if ($order->getsubtotal() > 0)
            return ($this->getMargin($order)) / $order->getbase_subtotal() * 100;
        else
            return 0;
    }

}