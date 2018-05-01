<?php

class MDN_SmartReport_Block_Report_Renderer_Table extends MDN_SmartReport_Block_Report_Renderer_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('SmartReport/Report/Renderer/Table.phtml');
    }

    protected function getReportDatas($limit = null)
    {
        return parent::getReportDatas(13);
    }

    protected function getColumns()
    {
        $columns = array();

        $datas = $this->getReportDatas(13);
        if (count($datas) > 0)
        {
            foreach($datas[0] as $k => $v)
            {
                $settings = $this->getColumnSettings($k);
                if (!isset($settings['hidden']))
                    $columns[] = $k;
            }
        }

            return $columns;
    }

    protected function getColumnSettings($columnKey)
    {
        $columnKey = str_replace(' ', '_', $columnKey);
        $settings = (array)$this->getReport()->getTable();
        if (!isset($settings['columns']))
            return;
        $columns = (array)$settings['columns'];
        if (!isset($columns[$columnKey]))
            return;
        $column = (array)$columns[$columnKey];

        return $column;
    }

    protected function getColumnSetting($columnKey, $settingName, $default = null)
    {

        $settings = $this->getColumnSettings($columnKey);
        if ($settings)
        {
            if (isset($settings[$settingName]))
                return $settings[$settingName];
        }

        return $default;
    }



    protected function getColumnHtmlAttributes($columnKey)
    {
        $html = "";

        $column = $this->getColumnSettings($columnKey);
        if (!$column)
            return;

        if (isset($column['align']))
            $html = ' align="'.$column['align'].'"';

        return $html;
    }

    protected function renderCell($columnKey, $value, $row)
    {

        $column = $this->getColumnSettings($columnKey);
        if ($column)
        {
            if (isset($column['renderer']))
            {
                switch($column['renderer'])
                {
                    case 'link':

                        $url = $this->getUrl($column['url'], array($column['param_name'] => $row[$column['param_value']]));
                        $value = '<a href="'.$url.'">'.$value.'</a>';
                        break;
                }
            }
        }

        return $value;
    }

    public function onClick()
    {
        $params = array('report_id' => $this->getReport()->getId());
        $params = array_merge($this->getReport()->getVariables(), $params);

        foreach($params as $k => $v) {
            if (!is_array($v))
                $params[$k] = urlencode($v);
        }

        $url = $this->getUrl('adminhtml/SmartReport_Reports/Extract', $params);
        $js = "openMyPopup('".$url."', '".$this->__($this->getReport()->getName())."')";

        return $js;
    }

    public function needTotalRow()
    {
        foreach($this->getColumns() as $column)
        {
            $settings = $this->getColumnSettings($column);
            if (isset($settings['total']))
                return true;
        }
    }

    public function getColumnTotal($column)
    {
        $total = '';
        $settings = $this->getColumnSettings($column);
        if (isset($settings['total']))
        {
            $total = 0;
            $collection = $this->getReportDatas();
            foreach($collection as $item)
            {
                $total += $item[$column];
            }
        }
        return $total;
    }

}
