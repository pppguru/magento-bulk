<?php

class MDN_AdvancedStock_Model_Pdf_StockTransfer extends MDN_AdvancedStock_Model_Pdf_Pdfhelper {

    public function getPdf($transfers = array()) {

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        foreach ($transfers as $transfer) {

            //add new page
            $titre = mage::helper('AdvancedStock')->__('Transfer "%s"', $transfer->getst_name());
            $settings = array();
            $settings['title'] = $titre;
            $settings['store_id'] = 0;
            $page = $this->NewPage($settings);
            $this->defineFont($page,10,self::FONT_MODE_BOLD);

            //add transfer information
            //product table header
            $this->drawTableHeader($page);

            $this->y -=10;

            $sourceWarehouse = $transfer->getSourceWarehouse();
            $targetWarehouse = $transfer->getTargetWarehouse();

            //print products
            foreach ($transfer->getProducts() as $product) {

                //draw product information
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
                $this->defineFont($page,10);

                $skuForDisplay = $this->WrapTextToWidth($page, $product->getstp_product_sku(), 100);
                $offset = $this->DrawMultilineText($page, $skuForDisplay, 15, $this->y, 10, 0.2, 10);
                $this->y -=10 + $offset;

                $skuForDisplay = $this->WrapTextToWidth($page, $product->getstp_product_name(), 190);
                $offset = $this->DrawMultilineText($page, $skuForDisplay, 120, $this->y, 10, 0.2, 10);
                $this->y -=10 + $offset;

                $ean = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product->getstp_product_id());
                $page->drawText($this->TruncateTextToWidth($page, $ean, 80), 320, $this->y, 'UTF-8');
                $page->drawText($product->getstp_qty_requested(), 400, $this->y, 'UTF-8');

                //add locations
                $srcLocation = $sourceWarehouse->getProductLocation($product->getstp_product_id());
                $tgtLocation = $targetWarehouse->getProductLocation($product->getstp_product_id());
                $page->drawText($srcLocation, 440, $this->y, 'UTF-8');
                $page->drawText($tgtLocation, 520, $this->y, 'UTF-8');

                $this->y -= $this->_ITEM_HEIGHT;

                //add new page if bottom reached
                if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                    $this->drawFooter($page);
                    $page = $this->NewPage($settings);
                    $this->drawTableHeader($page);
                }
            }


            //footer
            $this->drawFooter($page);
        }

        //add pages
        $this->AddPagination($this->pdf);
        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * Dessine l'entete du tableau avec la liste des produits
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        //entetes de colonnes
        $this->y -= 15;
        $this->defineFont($page,10);

        $page->drawText(mage::helper('AdvancedStock')->__('Sku'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('AdvancedStock')->__('Name'), 120, $this->y, 'UTF-8');
        $page->drawText(mage::helper('AdvancedStock')->__('Ean'), 320, $this->y, 'UTF-8');
        $page->drawText(mage::helper('AdvancedStock')->__('Qty'), 400, $this->y, 'UTF-8');
        $page->drawText(mage::helper('AdvancedStock')->__('Src location'), 440, $this->y, 'UTF-8');
        $page->drawText(mage::helper('AdvancedStock')->__('Tgt location'), 520, $this->y, 'UTF-8');

        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

}