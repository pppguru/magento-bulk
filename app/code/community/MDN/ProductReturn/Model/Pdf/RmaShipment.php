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
class MDN_ProductReturn_Model_Pdf_RmaShipment extends MDN_ProductReturn_Model_Pdf_Pdfhelper
{

    public function getPdf($orders = array())
    {

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        foreach ($orders as $order) {
            //retrieve rma
            $rma = mage::helper('ProductReturn')->getRmaFromGeneratedOrder($order);
            if ($rma == null)
                continue;

            //new page
            $titre                = mage::helper('ProductReturn')->__('Product Return #%s', $rma->getrma_ref());
            $settings             = array();
            $settings['title']    = $titre;
            $settings['store_id'] = 0;
            $page                 = $this->NewPage($settings);

            //addresses and main information
            //$txt_date = mage::helper('ProductReturn')->__('Valid until : ').date('d/m/Y', strtotime($rma->getrma_expire_date()));
            $txt_date   = mage::helper('ProductReturn')->__('Date : %s', mage::helper('core')->formatDate($rma->getrma_created_at(), 'short'));
            $valuStatus = $rma->getrma_status();
            //$txtStatus = mage::helper('ProductReturn')->__('Status : ').mage::helper('ProductReturn')->__($valuStatus);
            $txtStatus       = mage::helper('ProductReturn')->__('Order : %s', $rma->getSalesOrder()->getincrement_id());
            $myAddress       = Mage::getStoreConfig('productreturn/pdf/address');
            $customerAddress = $rma->getShippingAddress()->getFormated();
            $this->AddAddressesBlock($page, $myAddress, $customerAddress, $txt_date, $txtStatus);
            $this->y -= 20;

            //display product table header
            $this->drawTableHeader($page);

            $this->y -= 10;

            //display products (from order)
            foreach ($order->getAllItems() as $product) {
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
                $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

                //retrieve information
                $productName = $product->getname();
                $reason      = $rma->getReasonForProductId($product->getproduct_id());
                $action      = mage::helper('ProductReturn')->__($rma->getActionForProductId($product->getproduct_id()));

                //draw
                $productName = $this->TruncateTextToWidth($page, $productName, 220);
                $page->drawText($this->TruncateTextToWidth($page, $productName, 400), 15, $this->y, 'UTF-8');
                $this->drawTextInBlock($page, (int)$product->getqty_ordered(), 240, $this->y, 40, 20, 'c');
                $this->drawTextInBlock($page, $reason, 300, $this->y, 40, 20, 'l');
                $this->drawTextInBlock($page, $action, 430, $this->y, 40, 20, 'l');

                //next page
                if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                    $this->drawFooter($page);
                    $page = $this->NewPage($settings);
                    $this->drawTableHeader($page);
                }

            }

            //comments
            $this->y -= 50;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
            $this->y -= 15;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);

            $comments = $rma->getrma_public_description();
            if (!empty($comments)) {
                $page->drawText(mage::helper('ProductReturn')->__('Comments :'), 15, $this->y, 'UTF-8');
                $comments = $this->WrapTextToWidth($page, $comments, $this->_PAGE_WIDTH);
                $offset   = $this->DrawMultilineText($page, $comments, 15, $this->y - 20, 10, 0.2, 11);
                $this->y -= $offset;

                $this->y -= 35;
                $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
                $this->y -= 15;
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
            }

            //dessine le pied de page
            $this->drawFooter($page);
        }

        //rajoute la pagination
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        return $this->pdf;
    }


    /**
     * Dessine l'entete du tableau avec la liste des produits
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page)
    {

        //entetes de colonnes
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

        $page->drawText(mage::helper('ProductReturn')->__('Product'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Qty'), 250, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Reason'), 300, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Action'), 430, $this->y, 'UTF-8');

        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

}