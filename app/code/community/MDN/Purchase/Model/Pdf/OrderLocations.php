<?php

/**
 * Purchase Order Locations PDF
 *
 */
class MDN_Purchase_Model_Pdf_OrderLocations extends MDN_Purchase_Model_Pdf_Pdfhelper {

    private $_showPictures = false;
    private $_pictureSize = 70;

    const FONT_SIZE = 10;

    public function getPdf($orders = array()) {
        $this->initLocale($orders);

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);
       
        foreach ($orders as $order) {

            //add new page
            $titre = mage::helper('purchase')->__('Purchase Order');
            $settings = array();
            $settings['title'] = $titre;
            $settings['store_id'] = 0;
            $page = $this->NewPage($settings);
            $this->defineFont($page,self::FONT_SIZE,self::FONT_MODE_BOLD);

            //page header
            $txt_date = "Date :  " . date('d/m/Y', strtotime($order->getpo_date()));
            $txt_order = "No " . $order->getpo_order_id();
            $adresse_fournisseur = $order->getSupplier()->getAddressAsText();
            $adresse_client = $order->getTargetWarehouse()->getstock_address();
            $this->AddAddressesBlock($page, $adresse_fournisseur, $adresse_client, $txt_date, $txt_order);

            //table header
            $this->drawTableHeader($page);

            $this->y -=10;
            $warehouse = $order->getTargetWarehouse();

            //first loop to store location & product information
            $products = array();
            foreach ($order->getProducts() as $item) {
                $product = array();

                $product['location'] = $warehouse->getProductLocation($item->getpop_product_id());
                $product['sku'] = $item->getSku();
                $product['name'] = $item->getpop_product_name();
                $product['qty'] = $item->getpop_qty();

                $products[] = $product;
            }

            //sort by location
            usort($products, array("MDN_Purchase_Model_Pdf_OrderLocations", "sortProductsPerLocation"));

            //Display products
            foreach ($products as $item) {

                //font initialization
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
                $this->defineFont($page,self::FONT_SIZE);

                //location
                $this->drawTextInBlock($page, $item['location'], 15, $this->y, 40, 20, 'l');

                //qty
                $this->drawTextInBlock($page, (int) $item['qty'], 105, $this->y, 40, 20, 'c');

                //sku
                $page->drawText($this->TruncateTextToWidth($page, $item['sku'], 95), 170, $this->y, 'UTF-8');

                //name
                $caption = $this->WrapTextToWidth($page, $item['name'], 280);
                $offset = $this->DrawMultilineText($page, $caption, 300, $this->y, 10, 0.2, 11);

                $this->y -= $offset + 20;

                //new page if required
                if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                    $this->drawFooter($page);
                    $page = $this->NewPage($settings);
                    $this->drawTableHeader($page);
                }
            }

            //new page if required
            if ($this->y < (150)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }

            //grey line
            $this->y -= 10;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

            //Draw footer
            $this->drawFooter($page);
        }

        //Display pages numbers
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        //reset language
        Mage::app()->getLocale()->revert();

        return $this->pdf;
    }

    /**
     * Products table header
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        $this->y -= 15;
        $this->defineFont($page,self::FONT_SIZE);

        $page->drawText(mage::helper('purchase')->__('Location'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Qty'), 125, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Sku'), 170, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Product'), 300, $this->y, 'UTF-8');

        //grey line
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

    /**
     * Sort product by locations & sku
     *
     */
    public static function sortProductsPerLocation($a, $b) {
        if ($a['location'] == $b['location']) {
            if ($a['sku'] < $b['sku'])
                return -1;
            else
                return 1;
        }
        else {
            if ($a['location'] < $b['location'])
                return -1;
            else
                return 1;
        }
    }

    /**
     * init pdf locale depending of supplier locale
     *
     */
    protected function initLocale($orders) {
        //consider only first order
        foreach ($orders as $order) {
            $localeId = $order->getSupplier()->getsup_locale();
            if ($localeId)
                Mage::app()->getLocale()->emulateLocale($localeId);
        }
    }

}