<?php

class MDN_SmartReport_Block_Report_Extract extends Mage_Adminhtml_Block_Template
{
    protected $_report = null;

    public function getReport()
    {
        if ($this->_report == null)
        {

            $this->_report = Mage::getModel('SmartReport/Report')->getReportById($this->getRequest()->getParam('report_id'));
            $this->_report->setVariables(Mage::helper('SmartReport')->getVariables());
        }
        return $this->_report;
    }

    protected function getCollection()
    {
        return $this->getReport()->getReportDatas();
    }

    protected function getColumns()
    {
        $columns = array();

        $datas = $this->getReport()->getReportDatas();
        if (count($datas) > 0)
        {
            foreach($datas[0] as $k => $v)
            {
                $columns[] = $k;
            }
        }

        return $columns;
    }

    public function getDownloadUrl()
    {
        $params = Mage::helper('SmartReport')->getVariables();

        foreach($params as $k => $v)
            $params[$k] = urlencode($v);

        return Mage::helper('adminhtml')->getUrl('*/*/ExtractExcel', $params);
    }

}
