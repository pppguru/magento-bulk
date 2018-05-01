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

class MDN_ProductReturn_Model_Pdf_PendingProducts extends MDN_ProductReturn_Model_Pdf_Pdfhelper
{

    public function getPdf($productIds = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $this->pdf = new Zend_Pdf();

        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        //new page
        $titre                = mage::helper('ProductReturn')->__('Products to process');
        $settings             = array();
        $settings['title']    = $titre;
        $settings['store_id'] = 0;
        $page                 = $this->NewPage($settings);

        //display product table header
        $this->drawTableHeader($page);

        //load collection
        $collection = mage::getModel('ProductReturn/RmaProducts')
            ->getCollection()
            ->join('ProductReturn/Rma', 'rma_id=rp_rma_id')
            ->join('catalog/product', 'rp_product_id=entity_id')
            ->addFieldToFilter('rp_id', array('in' => $productIds));

        foreach ($collection as $product) {
            $this->y -= 15;
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

            $page->drawText($product->getrma_ref(), 15, $this->y, 'UTF-8');
            $page->drawText($product->getsku(), 100, $this->y, 'UTF-8');
            $page->drawText($product->getrp_product_name(), 200, $this->y, 'UTF-8');
            $page->drawText($product->getrp_qty(), 400, $this->y, 'UTF-8');
            $page->drawText(mage::helper('ProductReturn')->__($product->getrp_destination()), 450, $this->y, 'UTF-8');

            $this->y -= $this->_ITEM_HEIGHT;

            //next page
            if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }

        }

        //si on a plus la place de rajouter le footer, on change de page
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


    /**
     * Dessine l'entete du tableau avec la liste des produits
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page)
    {

        //entetes de colonnes
        $this->y -= 15;
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

        $page->drawText(mage::helper('ProductReturn')->__('Rma #'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Sku'), 100, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Product'), 200, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Qty'), 400, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Destination'), 450, $this->y, 'UTF-8');

        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

}