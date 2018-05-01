<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Product Feeds
 * @version   1.1.2
 * @build     428
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_FeedExport_Model_Feed_Generator_Pattern_Review
     extends Mirasvit_FeedExport_Model_Feed_Generator_Pattern
{
    private $_loadedProducts = array();

    public function getValue($pattern, $review)
    {
        $value = null;
        $pattern = $this->parsePattern($pattern);

        switch($pattern['key']) {
            case 'product_name':
                $product = $this->getProduct($review);
                $value      = $product->getName();
                break;

            case 'product_url':
                $product = $this->getProduct($review);
                $value      = $product->getProductUrl(false);
                break;

            case 'sku':
                $product = $this->getProduct($review);
                $value      = $product->getSku();
                break;

            case 'manufacturer':
                $product = $this->getProduct($review);
                $value      = $product->getManufacturer();
                break;

            case 'review_url':
                $product = $this->getProduct($review);
                $value      = Mage::getUrl('review/product/view', array('id'=> $review->getReviewId()));
                break;

            case 'rating':
                $value = $this->getAveregeRating($review);
                break;

            default:
                $value = $review->getData($pattern['key']);
        }

        $value = $this->applyFormatters($pattern, $value);

        return $value;
    }

    public function getProduct($review)
    {
        try
        {
            if(!isset($this->_loadedProducts[$review->getData('entity_pk_value')])) {
                if(Mage::getModel('catalog/product')->load($review->getEntityPkValue())) {
                    $this->_loadedProducts[$review->getEntityPkValue()] = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
                } else {
                    throw new Exception('Product with id '.$review->getEntityPkValue().' does not exist. Review id: '.$review->getReviewId());
                }
            }
        } catch(Exception $e) {
            return $e;
        }

        return $this->_loadedProducts[$review->getEntityPkValue()];
    }

    public function getAveregeRating($review)
    {
        $avg = 5;

        $summary_data = Mage::getModel('review/review_summary')
            ->setStoreId($review->getStoreId())
            ->load($review->getEntityPkValue());

        if($summary_data->getRatingSummary()) {
            $avg = 0.05 * $summary_data->getRatingSummary();
        }

        return $avg;
    }
}