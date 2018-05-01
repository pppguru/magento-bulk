<?php

class MDN_SmartReport_Block_Report_Renderer_MultiBar extends MDN_SmartReport_Block_Report_Renderer_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('SmartReport/Report/Renderer/MultiBar.phtml');
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

    protected function getCategories()
    {
        $collection = parent::getReportDatas();
        $categories = array();
        foreach($collection as $row)
        {
            $categories[] = $row['x'];
        }
        return $categories;
    }

    protected function getSeries()
    {
        $collection = parent::getReportDatas();
        $series = array();

        foreach($collection as $row)
        {
            foreach($row as $k => $v)
            {
                if ($k == 'x')
                    continue;

                if (!isset($series[$k]))
                    $series[$k] = array();
                $series[$k][] = (int)$v;
            }

        }

        $datas = array();
        foreach($series as $name => $values)
        {
            $datas[] = array('name' => $name, 'data' => $values);
        }

        return $datas;
    }


}
