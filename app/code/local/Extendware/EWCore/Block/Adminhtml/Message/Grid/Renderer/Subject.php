<?php
class Extendware_EWCore_Block_Adminhtml_Message_Grid_Renderer_Subject extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return '<span class="grid-row-title">' . $row->getSubject() . '</span>'
            . ($row->getSummary() ? '<br />' . $row->getSummary() : '');
    }
}
