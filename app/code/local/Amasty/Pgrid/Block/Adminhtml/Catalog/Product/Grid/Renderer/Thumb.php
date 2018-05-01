<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Thumb extends Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        try
        {
            $size    = Mage::helper('ampgrid')->getGridThumbSize();

            if (!$row->getThumbnail())
            {
                $product = Mage::getModel('catalog/product')->load($row->getEntityId());
                if ($product)
                {
                    if ($product->getThumbnail())
                    {
                        $row->setThumbnail($product->getThumbnail());
                    }
                }
            }

            $url     = Mage::helper('catalog/image')->init($row, 'thumbnail')->resize($size)->__toString();
            $zoomUrl = '';
            if (Mage::getStoreConfig('ampgrid/attr/zoom'))
            {
                $zoomUrl = Mage::helper('catalog/image')->init($row, 'thumbnail')->__toString();
            }
            if ($url)
            {
                $html  = '';
                if ($zoomUrl)
                {
                    $html .= '<a href="' . $zoomUrl . '" rel="lightbox[zoom' . $row->getId() . ']">';
                }
                $html .= '<img src="' . $url . '" alt="" width="' . $size . '" height="' . $size . '" />';
                $html .= '</a>';
                return $html;
            }
        } catch (Exception $e) { /* no file uploaded */ }
        return '';
    }
}
