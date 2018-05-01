<?php

class MDN_SmartReport_Block_Report_Renderer_Bar extends MDN_SmartReport_Block_Report_Renderer_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('SmartReport/Report/Renderer/Bar.phtml');
    }

    protected function getReportDatas($limit = null)
    {
        $collection = parent::getReportDatas($limit);
        $datas = array();
        foreach($collection as $row)
        {
            $datas[] = array($row['x'], (int)$row['y']);
        }
        return $datas;

    }


}
