<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Classe pour l'impression d'un bon de commande fournisseur
 *
 */

class MDN_ProductReturn_Model_Pdf_SupplierReturn extends MDN_ProductReturn_Model_Pdf_Pdfhelper
{

    public function getPdf($rsr_array = array())
    {
        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);
        $rsr = $rsr_array[0];
        //$rsrp = $rsrps_array[0];

        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        $titre                = mage::helper('ProductReturn')->__('Supplier Return #%s', $rsr->getrsr_reference());
        $settings             = array();
        $settings['title']    = $titre;
        $settings['store_id'] = null;
        $page                 = $this->NewPage($settings);
        if ($rsr->getrsr_supplier_reference() == '' || $rsr->getrsr_supplier_reference() == null)
            $supplierReferenceTxt = '';
        else
            $supplierReferenceTxt = mage::helper('ProductReturn')->__("Supplier Reference: ") . $rsr->getrsr_supplier_reference();


        //addresses and main informations
        $txt_date = mage::helper('ProductReturn')->__('Date: %s', mage::helper('core')->formatDate($rsr->getrsr_created_at(), 'short'));

        $myAddress       = Mage::getStoreConfig('productreturn/pdf/address');
        $supplierAddress = $rsr->getFormatedSupplierAddress();
        $this->AddAddressesBlock($page, $myAddress, $supplierAddress, $txt_date, $supplierReferenceTxt);
        $this->y -= 20;
        $soustitre = mage::helper('ProductReturn')->__('Products List');
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 14);
        $this->drawTextInBlock($page, $soustitre, 0, $this->y, $this->_PAGE_WIDTH, 50, 'c');
        $this->y -= 10;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //display product table header
        $this->drawTableHeader($page);

        $this->y -= 10;

        foreach ($rsr->getProducts() as $rsrp) {

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

            $productName = substr($rsrp->getrsrp_product_name(), 0, 30);
            if (strlen($productName) == 30) $productName .= '...';
            $page->drawText($this->TruncateTextToWidth($page, $productName, 400), 15, $this->y, 'UTF-8');
            $productSupplierSku = mage::getModel('Purchase/ProductSupplier')
                ->loadForProductSupplier($rsrp->getrsrp_product_id(), $rsrp->getrsrp_sup_id())
                ->getpps_reference();
            $page->drawText($this->TruncateTextToWidth($page, $productSupplierSku, 45), 200, $this->y, 'UTF-8');

            $productSerial = $rsrp->getrsrp_serial();
            $page->drawText($this->TruncateTextToWidth($page, $productSerial, 80), 300, $this->y, 'UTF-8');


            $productComment = $rsrp->getrsrp_comments();
            $page->drawText($this->TruncateTextToWidth($page, $productComment, 230), 400, $this->y, 'UTF-8');
            $this->y -= 15;
            $purchaseOrderProduct = Mage::getModel('Purchase/OrderProduct')->load($rsrp->getrsrp_pop_id());
            $purchaseOrder        = $purchaseOrderProduct->getPurchaseOrder();
            $productInvoiceNum    = Mage::helper('ProductReturn')->__('Invoice Number: ') . $purchaseOrder->getpo_invoice_ref();
            $productInvoiceDate   = Mage::helper('ProductReturn')->__('Invoice Date: ') . $purchaseOrder->getpo_invoice_date();
            $page->drawText($this->TruncateTextToWidth($page, $productInvoiceNum, 300), 15, $this->y, 'UTF-8');
            $page->drawText($this->TruncateTextToWidth($page, $productInvoiceDate, 300), 250, $this->y, 'UTF-8');
            $this->y -= $this->_ITEM_HEIGHT;
            $comments = null;
            $offset   = $this->DrawMultilineText($page, $comments, 100, $this->y, 10, 0.2, 11);
            $this->y -= $this->_ITEM_HEIGHT + $offset;

            if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }
        }

        if ($this->y < (150)) {
            $this->drawFooter($page);
            $page = $this->NewPage($settings);
            $this->drawTableHeader($page);
        }

        //dessine le pied de page
        $this->drawFooter($page);

        //rajoute la pagination
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        return $this->pdf;
    }

    public function drawTableHeader(&$page)
    {
        //entetes de colonnes
        $this->y -= 15;
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

        $page->drawText(mage::helper('ProductReturn')->__('Product'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Supplier Sku'), 200, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Serial'), 300, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Comments'), 400, $this->y, 'UTF-8');

        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }
}