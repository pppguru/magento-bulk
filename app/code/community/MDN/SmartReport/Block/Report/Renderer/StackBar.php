<?php

class MDN_SmartReport_Block_Report_Renderer_StackBar extends MDN_SmartReport_Block_Report_Renderer_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('SmartReport/Report/Renderer/StackBar.phtml');
    }

    protected function getReportDatas($limit = null)
    {
        $collection = parent::getReportDatas($limit);

        $datas = array();

        if ($this->getReport()->getarea_sql_column() == 1)
        {
            foreach($collection as $item)
            {
                foreach($item as $k => $v)
                {
                    if (($k == 'x'))
                        continue;
                    if (!isset($datas[$k]))
                        $datas[$k] = array('name' => $k, 'data' => array());

                    $datas[$k]['data'][] = (int)$v;
                }
            }
            $datas = array_values($datas);
        }
        else
        {
            $allX = $this->getCategories();
            $allW = array();
            $allY = array();

            foreach($collection as $row)
            {
                if (!isset($row['w']))
                    $row['w'] = '';

                if (!in_array($row['w'], $allW))
                    $allW[] = $row['w'];

                $allY[$row['x'].'_'.$row['w']] = $row['y'];
            }

            foreach($allW as $w)
            {
                $item = array();
                $item['name'] = ($w ? $w : $this->getReport()->getYLabel());
                $item['data'] = array();

                foreach($allX as $x)
                {
                    if (isset($allY[$x.'_'.$w]))
                        $item['data'][] = (int)$allY[$x.'_'.$w];
                    else
                        $item['data'][] = 0;
                }
                $datas[] = $item;
            }

        }

        if ($this->getReport()->getcumulative())
        {
            for($i=0;$i<count($datas);$i++)
            {
                $sum = 0;
                for($j=0;$j<count($datas[$i]['data']);$j++)
                {
                    $sum += $datas[$i]['data'][$j];
                    $datas[$i]['data'][$j] = $sum;
                }
            }
        }

        return $datas;

    }

    protected function getCategories()
    {
        $categories = array();
        $datas = parent::getReportDatas();
        foreach($datas as $item)
        {
            if (!in_array($item['x'], $categories))
                $categories[] = $item['x'];
        }

        return $categories;
    }

}
