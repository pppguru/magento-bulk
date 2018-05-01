<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {

            $data *= $this->getColumn()->getprice_rate();

            $currency_code = $this->_getCurrencyCode($row);

            if (!$currency_code) {
                return $data;
            }

            $data = floatval($data) * $this->_getRate($row);
            $data = sprintf("%f", $data);
            $data = Mage::app()->getLocale()->currency($currency_code)->toCurrency($data);
            return $data;
        }
        return $this->getColumn()->getDefault();
    }

}