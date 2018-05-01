<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Abstract extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function renderHeader()
    {
        if (false !== $this->getColumn()->getGrid()->getSortable() && false !== $this->getColumn()->getSortable()) {
            $className = 'not-sort';
            $dir = strtolower($this->getColumn()->getDir());
            $nDir= ($dir=='asc') ? 'desc' : 'asc';
            if ($this->getColumn()->getDir()) {
                $className = 'sort-arrow-' . $dir;
            }
            $out = '<a href="#" name="' . $this->getColumn()->getId() . '" title="' . $nDir
                   . '" class="' . $className . '"><span class="sort-title">'
                   . $this->getColumn()->getHeader().'</span></a>';
        } else {
            $out = '<span id="am_abstract" name="' . $this->getColumn()->getId() . '" >'
                   . $this->getColumn()->getHeader().'</span>';
        }
        return $out;
    }
}
