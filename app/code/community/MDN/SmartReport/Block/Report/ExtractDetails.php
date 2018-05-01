<?php

class MDN_SmartReport_Block_Report_ExtractDetails extends MDN_SmartReport_Block_Report_Extract
{

    protected function getCollection()
    {
        return $this->getReport()->getReportDetails();
    }

    protected function getColumns()
    {
        $columns = array();

        $datas = $this->getReport()->getReportDetails();
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

        return Mage::helper('adminhtml')->getUrl('*/*/ExtractDetailsExcel', $params);
    }

}
