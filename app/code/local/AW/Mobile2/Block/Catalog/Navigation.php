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


class AW_Mobile2_Block_Catalog_Navigation extends Mage_Catalog_Block_Navigation
{
    public function getRootCategories()
    {
        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $activeCategories[] = $child;
            }
        }
        return $activeCategories;
    }

    public function getChildCategoriesHtml($category)
    {
        $activeChildren = $this->getActiveChildren($category);
        if (count($activeChildren) == 0) {
            return '';
        }
        $html = '<div class="nav__list nav__list--subcategory" data-parent-category="'
            . $category->getEntityId() . '">'
        ;
        $html .= '<ul class="nav__list-inner">';
        $categoryModel = Mage::getModel('catalog/category')->load($category->getEntityId());
        if ($this->_hasProducts($categoryModel) &&
            $categoryModel->getDisplayMode() != Mage_Catalog_Model_Category::DM_PAGE
        ) {
            $categoryAlias = $this->__('Category products');
            if (Mage::helper('aw_mobile2')->isIphoneTheme()) {
                $categoryAlias = $this->__('All');
            }
            $isCurrent = $this->getCurrentCategory()->getEntityId() === $categoryModel->getEntityId();
            $html .= '<li class="nav__item nav__item--products' . ($isCurrent?' is-current':'') . '">';
            $html .= '<a class="nav__item-title" href="javascript:setLocation(\'' . $this->getCategoryUrl($category) . '\');void(0);">'
                . $categoryAlias . '</a></li>'
            ;
        }
        foreach ($activeChildren as $child) {
            $activeChildChildren = $this->getActiveChildren($child);
            $isCurrent = $this->getCurrentCategory()->getEntityId() === $child->getEntityId();
            $itemClass = (count($activeChildChildren) ? 'nav__item--with-subcategory' : '')
                . ($isCurrent?' is-current':'')
            ;
            $itemUrl = count($activeChildChildren) ? 'javascript:void(0);' : $this->getCategoryUrl($child);
            $html .= '<li data-category="' . $child->getEntityId() . '" class="nav__item ' . $itemClass . '">';
            $html .= '<a title="' . $this->escapeHtml($child->getName()) . '" class="nav__item-title" href="javascript:setLocation(\'' . $itemUrl . '\');void(0);">'
                . $this->escapeHtml($child->getName()) . '</a></li>'
            ;
        }
        $html .= '</ul><div class="nav__list-shadow"></div></div>';
        foreach ($activeChildren as $child) {
            $html .= $this->getChildCategoriesHtml($child);
        }
        return $html;
    }

    public function getActiveChildren($category)
    {
        // get all children
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = (array)$category->getChildrenNodes();
        } else {
            $children = $category->getChildren();
        }

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }
        return $activeChildren;
    }

    protected function _hasProducts($categoryModel)
    {
        return (bool)$categoryModel->getProductCollection()->getSize();
    }
}