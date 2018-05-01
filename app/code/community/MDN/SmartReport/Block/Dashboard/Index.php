<?php


class MDN_SmartReport_Block_Dashboard_Index extends Mage_Adminhtml_Block_Widget_Container
{

    public function getReports()
    {
        return Mage::getModel('SmartReport/Report')->getCollection();
    }

    public function renderReport($report)
    {
        $block = $this->getLayout()->createBlock('SmartReport/Report_Renderer_Grid');
        $block->setReport($report);
        return $block->toHtml();
    }

}
