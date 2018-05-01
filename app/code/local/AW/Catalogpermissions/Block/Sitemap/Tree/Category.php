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
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Catalogpermissions_Block_Sitemap_Tree_Category extends Mage_Catalog_Block_Seo_Sitemap_Tree_Category
{

    protected function _prepareLayout()
    {

        return parent::_prepareLayout();
    }

    public function bindPager($pagerName)
    {
        $pager = $this->getLayout()->getBlock($pagerName);

        if ($pager) {
            $perPage = Mage::getStoreConfig(self::XML_PATH_LINES_PER_PAGE);
            $pager->setAvailableLimit(array($perPage => $perPage));
            $pager->setCollection($this->getCollection());
        }
    }

    public function prepareCategoriesToPages()
    {

        return $this;
    }

    public function getTreeCollection()
    {

        $collection = parent::getTreeCollection()
            ->addPathsFilter($this->_storeRootCategoryPath . '/');

        if (count(Mage::registry(AW_Catalogpermissions_Helper_Data::DIABLED_CATEGS_SCOPE)) > 0) {
            $collection->addFieldToFilter(
                'main_table.entity_id',
                array("nin" => Mage::registry(AW_Catalogpermissions_Helper_Data::DIABLED_CATEGS_SCOPE))
            );
        }

        return $collection;
    }

}
