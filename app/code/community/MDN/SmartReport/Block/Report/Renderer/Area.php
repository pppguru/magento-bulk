<?php

class MDN_SmartReport_Block_Report_Renderer_Area extends MDN_SmartReport_Block_Report_Renderer_StackBar
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('SmartReport/Report/Renderer/Area.phtml');
    }

}