<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Mobile2
 * @version    2.0.6
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile2_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    /**
     * @return array|Varien_Data_Collection
     */
    public function getGalleryImages()
    {
        if ($this->_isGalleryDisabled) {
            return array();
        }
        return $this->_getMediaGalleryImages($this->getProduct());
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    protected function _getMediaGalleryImages($product)
    {
        if (!$product->hasData('media_gallery_images') && is_array($product->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($product->getMediaGallery('images') as $image) {
                if ($image['disabled'] && !$this->_isBaseImage($image, $product)) {
                    continue;
                }
                $image['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                $images->addItem(new Varien_Object($image));
            }
            $product->setData('media_gallery_images', $images);
        }

        return $product->getData('media_gallery_images');
    }

    /**
     * @param array $image
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _isBaseImage($image, $product)
    {
        return (strcasecmp($product->getImage(), $image['file']) == 0);
    }
}
