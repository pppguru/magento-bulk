<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Helper_Category extends Mage_Core_Helper_Abstract
{
    protected $_path = array();
    
    public function getOptionsForFilter()
    {

        $parentId       = Mage_Catalog_Model_Category::TREE_ROOT_ID;
//        (0 != Mage::helper('ampgrid')->getStore()->getRootCategoryId() ? Mage::helper('ampgrid')->getStore()->getRootCategoryId() : Mage_Catalog_Model_Category::TREE_ROOT_ID);
        
        $category       = Mage::getModel('catalog/category');
        $parentCategory = $category->load($parentId);
        
        $this->_buildPath($parentCategory);

        $options = array();
        $options[0] = $this->__('- With no category');
        foreach ($this->_path as $i => $path)
        {
            $string = str_repeat(". ", max(0, ($path['level'] - 1) * 3)) . $path['name'];
            $options[$path['id']] = $string;
        }
        return $options;
    }
    
    protected function _buildPath($category)
    {
        if ($category->getName()) // main root category will have no name, so we'll not add it
        {
            $this->_path[] = array(
                'id'    => $category->getId(),
                'level' => $category->getLevel(),
                'name'  => $category->getName(),
            );
        }
        if ($category->hasChildren())
        {
            foreach ($this->getChildrenCategories($category) as $child)
            {
                $this->_buildPath($child);
            }
        }
    }
    
    public function getChildrenCategories($category)
    {
//        var_dump(get_class($category->getResource()));
//        $ids = implode(',', $category->getResource()->getChildren($category, FALSE, FALSE));
        
        $collection = $category->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
//            ->addAttributeToFilter('is_active', 1)
            ->addFieldToFilter('parent_id', $category->getId())
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->joinUrlRewrite()
            ->load();
        
        return $collection;
    }
}
