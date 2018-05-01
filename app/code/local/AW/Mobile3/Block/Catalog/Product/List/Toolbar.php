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
 * @package    AW_Mobile3
 * @version    3.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile3_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    const LIMIT = 20;
    public function getLimit()
    {
        return self::LIMIT;
    }

    public function canShowBlock()
    {
        $productListBlock = $this->getLayout()->getBlock('product_list');
        $catalogSearchResultBlock = $this->getLayout()->getBlock('search_result_list');
        $advancedSearchResultBlock = $this->getLayout()->getBlock('search_result_catalog');
        if ($catalogSearchResultBlock) {
            $productListBlock = $catalogSearchResultBlock;
        }
        if ($advancedSearchResultBlock) {
            $productListBlock = $advancedSearchResultBlock;
        }
        if ($productListBlock) {
            $collection = $productListBlock->getLoadedProductCollection();
            if ($collection && $collection->getSize() > 1) {
                return true;
            }
        }
        return false;
    }
}