<?php


class MDN_SmartReport_Block_Report_Type_Category extends MDN_SmartReport_Block_Report_Type
{
    protected $_category = null;

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->__('Category').' '.$this->getCategory()->getName();
    }

    public function getCategory()
    {
        if ($this->_category == null)
        {
            $vars = $this->getVariables();
            $this->_category = Mage::getModel('catalog/category')->load($vars['category_id']);
        }
        return $this->_category;
    }

    public function getFormHiddens()
    {
        return array('category_id' => $this->getCategory()->getId());
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/SmartReport_Reports/Category');
    }

    public function getAdditionalFilters()
    {
        $filters = array();

        $categoryFilter = '<select name="category_id" style="margin-right: 20px;">';
        $categories = array();
        $this->getCategoryTree($categories, 0, 0);
        foreach($categories as $k => $v)
        {
            $selected = ($k == $this->getCategory()->getId() ? ' selected ' : '');
            $categoryFilter .= '<option '.$selected.' value="'.$k.'">'.$v.'</option>';
        }
        $categoryFilter .= '</select>';

        $filters['Category'] = $categoryFilter;

        return $filters;
    }

    protected function getCategoryTree(&$array, $level, $parentId)
    {
        $categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('name')->addFieldToFilter('parent_id', $parentId);
        foreach($categories as $category)
        {
            $indent = str_repeat('-', $level * 4);
            $array[$category->getId()] = '|'.$indent.' '.$category->getName();
            $this->getCategoryTree($array, $level + 1, $category->getId());
        }
    }

}