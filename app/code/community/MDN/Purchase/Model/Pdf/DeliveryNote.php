<?php

class MDN_Purchase_Model_Pdf_DeliveryNote extends MDN_Purchase_Model_Pdf_Pdfhelper {

    private $_date = null;
    private $_po = null;

    const FONT_SIZE = 10;
    const LINE_SIZE = 10;

    const INITIAL_MARGIN = 15;
    const QTY_WIDTH = 30;
    const SKU_WIDTH = 180;
    const NAME_WIDTH = 270;
    const LOCATION_WIDTH = 120;

    /**
     * Main function to get the pdf
     * @param type $po
     * @param type $date
     */
    public function getPdfForDate($po, $date)
    {
        $this->_date = $date;
        $this->_po = $po;

        $lines = Mage::getSingleton('Purchase/Order_Delivery')->getDeliveredProducts($po, $date);

        return $this->getPdf($lines);
    }

    public function getPdf($lines = array()) {


        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        //add new page        
        $settings = array();
        $settings['title'] = mage::helper('purchase')->__('PO %s delivery : %s', $this->_po->getpo_order_id(), Mage::helper('core')->formatDate($this->_date, 'short', false));
        $settings['store_id'] = 0;
        $page = $this->NewPage($settings);
        $this->defineFont($page,self::FONT_SIZE,self::FONT_MODE_BOLD);



        //table header
        $this->drawTableHeader($page);

        foreach ($lines as $line) {

            $product = $line['product'];

            $x = self::INITIAL_MARGIN;

            $page->drawText($line['qty'], $x, $this->y, 'UTF-8');
            $x = $x + self::QTY_WIDTH;

            $sku = $this->WrapTextToWidth($page, $product->getSku(), self::NAME_WIDTH);
            $offset = $this->DrawMultilineText($page, $sku, $x, $this->y, 10, 0.2, self::FONT_SIZE);
            $x = $x + self::SKU_WIDTH;

            $name = $this->WrapTextToWidth($page, $product->getName(), self::NAME_WIDTH);
            $offset = $this->DrawMultilineText($page, $name, $x, $this->y, 10, 0.2, self::FONT_SIZE);
            $x = $x + self::NAME_WIDTH;

            $page->drawText($line['location'], $x, $this->y, 'UTF-8');
            $x = $x + self::LOCATION_WIDTH;

            $this->y -= self::INITIAL_MARGIN + 5;

            //new page if required
            if ($this->y < (150)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }

        }

        //Draw footer
        $this->drawFooter($page);

        //Display pages numbers
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * Draw headers
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        $x = self::INITIAL_MARGIN;
        $this->y -= self::INITIAL_MARGIN;

        $this->defineFont($page,self::FONT_SIZE);

        $page->drawText(mage::helper('purchase')->__('Qty'), $x, $this->y, 'UTF-8');
        $x = $x + self::QTY_WIDTH;

        $page->drawText(mage::helper('purchase')->__('Sku'), $x, $this->y, 'UTF-8');
        $x = $x + self::SKU_WIDTH;

        $page->drawText(mage::helper('purchase')->__('Product'), $x, $this->y, 'UTF-8');
        $x = $x + self::NAME_WIDTH;

        $page->drawText(mage::helper('purchase')->__('Location'), $x, $this->y, 'UTF-8');
        $x = $x + self::LOCATION_WIDTH;

        //grey bar
        $this->y -= 8;
        $page->drawLine(self::LINE_SIZE, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= self::INITIAL_MARGIN;
    }


}