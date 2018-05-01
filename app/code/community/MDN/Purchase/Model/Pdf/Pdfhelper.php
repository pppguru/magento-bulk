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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper pour les impressions PDF (entre autre quotaiton & facute
 * h�rite de la classe de MAGE pour rajouter des fonctionnalit� genre afficher entete, footer, alignement...
 */
abstract class MDN_Purchase_Model_Pdf_Pdfhelper extends Mage_Sales_Model_Order_Pdf_Abstract {

    protected $_BLOC_ENTETE_HAUTEUR = 50;
    protected $_BLOC_ENTETE_LARGEUR = 585;
    protected $_BLOC_FOOTER_HAUTEUR = 40;
    protected $_BLOC_FOOTER_LARGEUR = 585;
    protected $_LOGO_HAUTEUR = 40;
    protected $_LOGO_LARGEUR = 200;
    protected $_PAGE_HEIGHT = 842;
    protected $_PAGE_WIDTH = 595;
    protected $_ITEM_HEIGHT = 25;
    public $pdf;
    protected $firstPageIndex = 0;


    const FONT_MODE_BOLD = 'bold';
    const FONT_MODE_REGULAR = 'regular';
    const FONT_MODE_ITALIC = 'italic';
    /**
     * defien current font for the page
     * mode can be : regular, bold, italic
     *
     * @param type $page
     * @param type $size
     * @param type $mode
     */
    public function defineFont($page, $size, $mode = self::FONT_MODE_REGULAR){

        $trueType = Mage::getStoreConfig('purchase/general/pdf_use_truetype_font');

        switch ($mode){
            case self::FONT_MODE_REGULAR :
                ($trueType)?$this->_setFontRegular($page, $size):$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), $size);
                break;
            case self::FONT_MODE_BOLD :
                ($trueType)?$this->_setFontBold($page, $size):$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), $size);
                break;
            case self::FONT_MODE_ITALIC :
                ($trueType)?$this->_setFontItalic($page, $size):$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), $size);
                break;
        }
    }

    /**
     * Insert the logo in the PDF
     * The logo is the one defined in System -> configuration -> Sales -> Sales -> Invoice and Packing Slip Design
     *
     * @param PDF_Page $page
     */
    protected function insertLogo(&$page, $StoreId = null) {

        try {
            if(!$StoreId){
              $StoreId = Mage::app()->getStore()->getStoreId();
            }
            $image = Mage::getStoreConfig('sales/identity/logo', $StoreId);
            if ($image) {
                $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
                if (is_file($image)) {
                    $image = Zend_Pdf_Image::imageWithPath($image);
                    $page->drawImage($image, 25, 785, 25 + $this->_LOGO_LARGEUR, 785 + $this->_LOGO_HAUTEUR);
                }
            }
        }catch(Exception $ex){
            mage::logException($ex);
        }
    }

    /**
     * Dessine un texte multiligne
     * retourne la taille en hauteur totale
     */
    protected function DrawMultilineText(&$page, $Text, $x, $y, $size, $GrayScale, $LineHeight) {
        $offset = -$LineHeight;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($GrayScale));
        $this->defineFont($page,$size);
        foreach (explode("\n", $Text) as $value) {
            if (trim($value) !== '') {
                $page->drawText(trim(strip_tags($value)), $x, $y, 'UTF-8');
                $y -=$LineHeight;
                $offset += $LineHeight;
            }
        }
        return $offset;
    }

    /**
     * Retourne la largeur d'un text (par rapport � la police et la taille
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize) {
        try {
            //fix iconv issue
            $workingString = '';
            for ($i = 0; $i < strlen($string); $i++) {
                if (ord($string{$i}) < 128)
                    $workingString .= $string{$i};
            }

            $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $workingString);
            $characters = array();
            for ($i = 0; $i < strlen($drawingString); $i++) {
                $characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
            }
            $glyphs = $font->glyphNumbersForCharacters($characters);
            $widths = $font->widthsForGlyphs($glyphs);
            $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
            return $stringWidth;
        } catch (Exception $ex) {
            die("Erreur dans Mdn pdf helper m�thode widthForStringUsingFontSize avec string = " . $string . ' - ' . $ex->getMessage() . ' - ' . $ex->getTraceAsString());
        }
    }

    /**
     * Dessine du texte dans un bloc en permettant l'alignement horizontal
     *
     * @param unknown_type $page
     * @param unknown_type $text
     * @param unknown_type $x
     * @param unknown_type $y
     * @param unknown_type $width
     * @param unknown_type $height
     * @param unknown_type $alignment
     */
    public function drawTextInBlock(&$page, $text, $x, $y, $width, $height, $alignment = 'c', $encoding = 'UTF-8') {
        //$page->drawRectangle($x, $y, $x + $width, $y + $height, Zend_Pdf_Page::LINE_DASHING_SOLID);
        //recupere la largeur du texte
        $text_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
        switch ($alignment) {
            case 'c': //on centre le texte dans le bloc
                $x = $x + ($width / 2) - $text_width / 2;
                break;
            case 'r': //on aligne a droite
                $x = $x + $width - $text_width;
            case 'l': //on aligne a gauche
                $x = $x;
        }

        $page->drawText(trim(strip_tags($text)), $x, $y, $encoding);
    }

    /**
     * Dessine le pied de page
     *
     * @param unknown_type $page
     */
    public function drawFooter(&$page) {
      
        $StoreId = $this->_settings['store_id'];
        if(!$StoreId){
          $StoreId = Mage::app()->getStore()->getStoreId();
        }

        //BACK GROUND
        $color = 1; //WHITE
        $this->defineFont($page,10);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($color));
        $page->drawRectangle(10, $this->_BLOC_FOOTER_HAUTEUR + 15, $this->_BLOC_FOOTER_LARGEUR, 15, Zend_Pdf_Page::SHAPE_DRAW_FILL);

        //FOOTER TEXT
        $color = 0; //BLACK
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($color));
        $this->DrawMultilineText($page, Mage::getStoreConfig('purchase/general/footer_text', $StoreId), 20, $this->_BLOC_FOOTER_HAUTEUR, 10, 0, 15);
    }

    /**
     * Dessine l'entete de la page
     */
    public function drawHeader(&$page, $title, $StoreId = null) {
        
        if(!$StoreId){
          $StoreId = Mage::app()->getStore()->getStoreId();
        }

        $fontSize = 10;
        $rightMargin = 10;
        $lineWidth = 1.5;

        //BACK GROUND
        $color = 1; //WHITE
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($color));
        $page->drawRectangle($rightMargin, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y - $this->_BLOC_ENTETE_HAUTEUR, Zend_Pdf_Page::SHAPE_DRAW_FILL);

        //LOGO
        $this->insertLogo($page, $StoreId);

        //HEADER TEXT
        $color = 0.3; //DARK GREY
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($color));
        $this->defineFont($page,$fontSize,self::FONT_MODE_BOLD);
        $yShift = 10;
        $size = $this->DrawMultilineText($page, Mage::getStoreConfig('purchase/general/header_text', $StoreId), 300, $this->y - $yShift, $fontSize, 0, 15);


        //LINE BELOW HEADER TEXT
        $yShift = (($size + $yShift + 5)>$this->_BLOC_ENTETE_HAUTEUR + 5)?($size + $yShift + 5):($this->_BLOC_ENTETE_HAUTEUR + 5);
        $this->y -= $yShift;
        $color = 0; //BLACK
        $page->setLineWidth($lineWidth);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale($color));
        $page->drawLine($rightMargin, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //TITLE
        $fontSize = 20;
        $this->y -= $fontSize + 5;
        $name = $title;
        $color = 0.3; //DARK GREY
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($color));
        $this->defineFont($page,$fontSize,self::FONT_MODE_BOLD);
        $this->drawTextInBlock($page, $name, 0, $this->y, $this->_PAGE_WIDTH, 50, 'c');

        //GREY LINE BELOW TITLE
        $this->y -= 10;
        $page->setLineWidth($lineWidth);
        $page->drawLine($rightMargin, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
    }

    /**
     * Cree une nouvelle page (et dessine son entete)
     *
     */
    //public function NewPage($title, $StoreId = null)
    public function newPage(array $settings = array()) {
        $page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->pdf->pages[] = $page;

        //on place Y tout en haut
        $this->y = 830;

        //dessine l'entete
        $title = $settings['title'];
        $StoreId = $settings['store_id'];
        $this->drawHeader($page, $title, $StoreId);

        //retourne la page
        return $page;
    }

    /**
     * Raccourci un texte jusqu'a ce qu'il ait une taille inf�rieure � celle pass�e en parametre
     *
     * @param unknown_type $text
     * @param unknown_type $width
     */
    public function TruncateTextToWidth($page, $text, $width) {
        $current_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
        while ($current_width > $width) {
            $text = substr($text, 0, strlen($text) - 1);
            $current_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
        }
        return $text;
    }

    /**
     * cree des retours a la ligne � partir d'une chaine de caracteres pour que ces lignes tiennent dans la largeur d�finie
     *
     * @param unknown_type $text
     * @param unknown_type $width
     */
    public function WrapTextToWidth($page, $text, $width) {
        $t_words = explode(' ', $text);

        //if no space, fix
        if(count($t_words)==1){
            $t_words = str_split ( $text, (int)($width-1) );
        }

        $buffer = "";
        $current_line = "";
        for ($i = 0; $i < count($t_words); $i++) {
            //si on a la place d'ajouter le mot, on le fait
            if ($this->widthForStringUsingFontSize($current_line . ' ' . $t_words[$i], $page->getFont(), $page->getFontSize()) < $width)
                $current_line .= ' ' . $t_words[$i];
            else  //sinon on ajoute la ligne et on repart de 0
            {
                if (($current_line != '') && (strlen($current_line) > 2))
                    $buffer .= $current_line . "\n";
                $current_line = $t_words[$i];
            }

            //si le mot contient un retour a la ligne, on remet la ligne courante
            if (strpos($t_words[$i], "\n") === false) {
                
            } else {
                if (($current_line != '') && (strlen($current_line) > 2))
                    $buffer .= $current_line;
                $current_line = '';
            }
        }
        $buffer .= $current_line;

        return $buffer;
    }

    /**
     * Rajoute la pagination
     *
     */
    public function AddPagination($pdf) {
        //pour chaque page
        $page_count = count($pdf->pages);
        for ($i = 0; $i < $page_count; $i++) {
            if ($i >= $this->firstPageIndex) {
                //recup la page
                $page = $pdf->pages[$i];
                //dessine la pagination
                $pagination = 'Page ' . ($i + 1 - $this->firstPageIndex) . ' / ' . ($page_count - $this->firstPageIndex);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                $this->defineFont($page,10);
                $this->drawTextInBlock($page, $pagination, 0, 25, $this->_PAGE_WIDTH - 20, 40, 'r');
            }
        }
    }

    /**
     * Dessine le bloc avec les adresses et les infos g�n�rales
     *
     */
    public function AddAddressesBlock(&$page, $LeftAddress, $RightAddress, $TxtDate, $TxtInfo) {

        $yMargin = 10;
        $rightMargin = 10;
        $defaultYShiftForAddresses = 90;
        $page->setLineWidth(1.5);

        //DATE AND INFOS
        $this->y -= 2 * $yMargin;
        $this->defineFont($page,12);
        $page->drawText($TxtDate, 25, $this->y, 'UTF-8');
        $page->drawText($TxtInfo, $this->_PAGE_WIDTH / 2 + $rightMargin, $this->y, 'UTF-8');

        //LINE UPPER THE  ADDRESSES
        $this->y -= $yMargin;
        $page->drawLine($rightMargin, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //ADDRESSES
        $this->y -=  2 * $yMargin;
        $addressesFontSize = 14;
        $leftYShift = $this->DrawMultilineText($page, $LeftAddress, 25, $this->y, $addressesFontSize, 0.4, $addressesFontSize + 2);
        $rightYShift = $this->DrawMultilineText($page, $RightAddress, $this->_PAGE_WIDTH / 2 + $rightMargin, $this->y, $addressesFontSize, 0.4, $addressesFontSize + 2);

        //LINE BELOW ADDRESSES
        $addressesFinalHeight = max($leftYShift,$rightYShift,$defaultYShiftForAddresses) + $yMargin;
        $this->y -= $addressesFinalHeight;
        $page->drawLine($rightMargin, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //VERTICAL LINE BETWEEN THE ADDRESSES
        $page->drawLine($this->_PAGE_WIDTH / 2, $this->y + $addressesFinalHeight + 50, $this->_PAGE_WIDTH / 2, $this->y);
    }

    public function FormatAddress($adress, $caption = '', $show_details = false, $NoTvaIntraco = '') {
        if ($NoTvaIntraco == 'taxvat')
            $NoTvaIntraco = '';
        $FormatedAddress = "";
        if ($caption != '')
            $FormatedAddress = $caption . "\n ";
        if ($adress != null) {
            if ($adress->getcompany() != '')
                $FormatedAddress .= $adress->getcompany() . "\n ";
            if ($adress->getPrefix() != '')
                $FormatedAddress .= $adress->getPrefix(). " ";
            $FormatedAddress .= $adress->getName() . "\n ";
            $FormatedAddress .= $adress->getStreet(1) . "\n ";
            if ($adress->getStreet(2) != '')
                $FormatedAddress .= $adress->getStreet(2) . "\n ";            

            if ($show_details) {
                if ($adress->getbuilding() != '')
                    $FormatedAddress .= $adress->getbuilding();
                if ($adress->getfloor() != '')
                    $FormatedAddress .= $adress->getfloor();
                if ($adress->getdoor_code() != '')
                    $FormatedAddress .= ' Code ' . $adress->getdoor_code();
                if ($adress->getappartment() != '')
                    $FormatedAddress .= $adress->getappartment();
                $FormatedAddress .= "\n ";
            }

            $FormatedAddress .= $adress->getPostcode() . ' ' . $adress->getCity() . ' ' . $adress->getRegion()."\n ";

            $FormatedAddress .= strtoupper(Mage::getModel('directory/country')->load($adress->getCountry())->getName()) . "\n ";
            if ($show_details)
                $FormatedAddress .= $adress->getcomments() . "\n ";
            if ($NoTvaIntraco != '')
                $FormatedAddress .= 'VAT : ' . $NoTvaIntraco;
        }
        return $FormatedAddress;
    }

    protected function pngToZendImage($pngImage) {
        //save png image to disk
        $path = Mage::getBaseDir() . DS . 'var' . DS . 'barcode_image.png';
        imagepng($pngImage, $path);

        //create zend picture
        $zendPicture = Zend_Pdf_Image::imageWithPath($path);

        //delete file
        unlink($path);

        //return
        return $zendPicture;
    }

}
