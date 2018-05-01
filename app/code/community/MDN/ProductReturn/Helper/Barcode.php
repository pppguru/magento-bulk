<?php

class MDN_ProductReturn_Helper_Barcode extends Mage_Core_Helper_Abstract {



    /**
     * Return barcode picture
     *
     * @param unknown_type $barcode
     */
    public function getBarcodePicture($barcode) {
        $image = null;
        if(!empty($barcode)){
            $barcode = trim($barcode);
        }else{
            return $image;
        }
        $barcodeOptions = array('text' => trim($barcode));
        $rendererOptions = array();
        if (class_exists('Zend_Barcode'))
        {
            $factory = Zend_Barcode::factory(
                'Code128', 'image', $barcodeOptions, $rendererOptions
            );
            $image = $factory->draw();
        }
        else
        {
            $image = null;
        }
        return $image;
    }


    public function pngToZendImage($pngImage) {

        //save png image to disk
        $path = Mage::getBaseDir() . DS . 'var' . DS . 'barcode_image.png';
        imagepng($pngImage, $path);

        //create zend picture
        $zendPicture = Zend_Pdf_Image::imageWithPath($path);

        //delete file
        if (file_exists($path))
            unlink($path);

        //return
        return $zendPicture;
    }

}