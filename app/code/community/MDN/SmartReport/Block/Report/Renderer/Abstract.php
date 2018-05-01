<?php

class MDN_SmartReport_Block_Report_Renderer_Abstract extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function getReportDatas($limit = null)
    {
        $datas = $this->getReport()->getReportDatas($limit);

        return $datas;
    }

    public function hasReportData()
    {
        return (count($this->getReportDatas()) > 0);
    }

    public function getContainerStyle()
    {
        $style = "width: 100%; margin: 0 auto; float: center; margin-bottom: 25px;";
        $style .= "background-color: #e8e8e8; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;";
        return $style;
    }

    public function getYLabel()
    {
        return $this->getReport()->gety_label();
    }

    public function getLabelFormatter()
    {
        return "
                var pipePos = this.value.toString().indexOf('|');
                if (pipePos)
                {
                    return this.value.toString().substring(pipePos + 1);
                }
                else
                    return this.value.toString();
            ";
    }

    public function onClick()
    {
        $js = "
                var label = (this.category ? this.category : this.name);
                var pipePos = label.toString().indexOf('|');
                var id = label.toString();
                if (pipePos > 0)
                {
                    id = label.toString().substring(0, pipePos);
                    label = label.toString().substring(pipePos + 1);
                }
                ";

        $onclick = (array)$this->getReport()->getonclick();
        if (isset($onclick['action']))
        {
            $params = array($onclick['param_name'] => '--id--', 'report_id' => $this->getReport()->getId());
            $params = array_merge($this->getReport()->getVariables(), $params);

            foreach($params as $k => $v)
            {
                if (!is_array($v))
                    $params[$k] = urlencode($v);
            }

            $url = $this->getUrl($onclick['url'], $params);

            $js .= "var url = '".$url."';";
            $js .= "var title = '".$this->getReport()->getName()." - ' + label;";
            $js .= "url = url.replace('--id--', id);";
            switch($onclick['action'])
            {
                case 'goto_url':
                    $js .= "setLocation(url);";
                    break;
                case 'popup_url':
                    $js .= 'openMyPopup(url, title)';
                    break;
            }
        }

        return $js;
    }

    public function exportJs()
    {
        $params = array('report_id' => $this->getReport()->getId());
        $params = array_merge($this->getReport()->getVariables(), $params);

        foreach($params as $k => $v)
        {
            if (!is_array($v))
                $params[$k] = urlencode($v);
        }

        $url = $this->getUrl('adminhtml/SmartReport_Reports/Extract', $params);
        $js = "openMyPopup('".$url."', '".$this->getReport()->getName()."')";

        return $js;
    }

    public function getSubtitle()
    {
        $onclick = (array)$this->getReport()->getonclick();
        if (isset($onclick['action']))
        {
            return '('.$this->__('Click on an item to get more details').')';
        }

    }

}
