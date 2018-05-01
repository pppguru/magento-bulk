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


class AW_Mobile3_Block_Catalog_Navigation extends Mage_Catalog_Block_Navigation
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

    public function getChildCategoriesHtml($categoryKey, $category)
    {
        $activeChildren = $this->getActiveChildren($category);
        if (count($activeChildren) == 0) {
            return '';
        }
        $html = '<div class="panel__container" data-container="'
            . $categoryKey . '" data-title="'.$category->getName().'">'
        ;
        $html .= '<ul class="list-group">';
        $categoryModel = Mage::getModel('catalog/category')->load($category->getEntityId());
        if ($this->_hasProducts($categoryModel) &&
            $categoryModel->getDisplayMode() != Mage_Catalog_Model_Category::DM_PAGE
        ) {
            $categoryAlias = $this->__('Category products');
            if (Mage::helper('aw_mobile3')->isIphoneTheme()) {
                $categoryAlias = $this->__('All');
            }

            $isCurrent = $this->getCurrentCategory()->getEntityId() === $categoryModel->getEntityId();
            $html .= '<li class="list-group__item">';
            $html .= '<a class="list-group__item-title'. ($isCurrent?' is-current':'') .'" href="' . $this->getCategoryUrl($category) . '">'
                . $categoryAlias . ($isCurrent ? '<svg class="list-group__icon list-group__icon--current-nav svg-icon"><use xlink:href="#icon-checkmark" /></svg>':'') . '</a></li>'
            ;
        }
        foreach ($activeChildren as $key => $child) {
            $activeChildChildren = $this->getActiveChildren($child);
            $isCurrent = $this->getCurrentCategory()->getEntityId() === $child->getEntityId();

            $itemUrl = count($activeChildChildren) ? '' : $this->getCategoryUrl($child);
            $html .= '<li class="list-group__item ' . (count($activeChildChildren) ? 'list-group__item--with-sublist' : '') . '">';
            $html .= '<a '  . (count($activeChildChildren) ? 'data-open-container="'.$categoryKey.'-'.($key+1).'"' : '')
                            . 'title="' . $this->escapeHtml($child->getName())
                            . '" class="list-group__item-title'. ($isCurrent && !count($activeChildChildren) ? ' is-current':'') .'" href="' . $itemUrl . '">'
                . $this->escapeHtml($child->getName())
                . ($isCurrent && !count($activeChildChildren)  ? '<svg class="list-group__icon list-group__icon--current-nav svg-icon"><use xlink:href="#icon-checkmark" /></svg>':'')
                . (count($activeChildChildren) > 0 ? '<svg class="list-group__icon list-group__icon--arrow svg-icon"><use xlink:href="#icon-list-arrow" /></svg>' : '')
                . '</a></li>'
            ;
        }
        $html .= '</ul><div class="panel__container-shadow"></div></div>';
        foreach ($activeChildren as $key => $child) {
            $html .= $this->getChildCategoriesHtml($categoryKey.'-'.($key+1), $child);
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
