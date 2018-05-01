<?php

/**
 * Export purchase order to csv format
 *
 */
class MDN_Purchase_Model_Order_Csv  extends Mage_Core_Model_Abstract
{
    private $_order;
    private $_endLine = "\r\n";

    /**
     * Set purchase order
     * @param <type> $order
     * @return MDN_Purchase_Model_Order_Csv
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Return file name
     * @return <type>
     */
    public function getFileName()
    {
        return mage::helper('purchase')->__('Purchase Order #%s from %s', $this->_order->getpo_order_id(), $this->_order->getSupplier()->getsup_name()).'.csv';
    }

    /**
     * return content type
     */
    public function getContentType()
    {
        return 'text/csv';
    }

    /**
     * Return csv content
     */
    public function getCsv()
    {
        $template = 'sku;supplier_sku;name;qty;unit_price;discount;tax_rate;subtotal;total'.$this->_endLine;
        $csv = $template;
        
        foreach($this->_order->getProducts() as $product)
        {
            $line = $template;

            $line = str_replace('supplier_sku', $product->getpop_supplier_ref(), $line);
            $line = str_replace('sku', $product->getsku(), $line);
            $line = str_replace('name', $product->getpop_product_name(), $line);
            $line = str_replace('qty', $product->getpop_qty(), $line);
            $line = str_replace('unit_price', $product->getpop_price_ht(), $line);
            $line = str_replace('discount', $product->getDiscountLevel(), $line);
            $line = str_replace('tax_rate', $product->getpop_tax_rate(), $line);
            $line = str_replace('subtotal', $product->getRowTotal(), $line);
            $line = str_replace('total', $product->getRowTotalWithTaxes(), $line);

            $csv .= $line;
        }

        return $csv;
    }

}