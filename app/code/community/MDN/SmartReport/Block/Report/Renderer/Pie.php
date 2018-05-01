<?php

class MDN_SmartReport_Block_Report_Renderer_Pie extends MDN_SmartReport_Block_Report_Renderer_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('SmartReport/Report/Renderer/Pie.phtml');
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

    public function getLabelFormatter()
    {
        return "
                var pipePos = this.point.name.toString().indexOf('|');
                var label = '';
                if (pipePos)
                {
                    label = this.point.name.toString().substring(pipePos + 1);
                }
                else
                    label =  this.point.name.toString();
                label += ' - ' + parseInt(this.percentage) + '%';
                return label
            ";
    }

    public function getContainerStyle()
    {
        $style = "margin: 0 auto; float: center; margin-bottom: 25px;";
        $style .= "background-color: #e8e8e8; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;";
        return $style;
    }


}
