<?php


class MDN_SmartReport_Block_Report_Type_Dashboard extends MDN_SmartReport_Block_Report_Type
{

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->__('Dashboard').' ';
    }

    public function getReports()
    {
        $reports = array();

        $reportIds = explode(',', Mage::getStoreConfig('smartreport/dashboard/reports'));

        foreach($reportIds as $id)
        {
            if ($r = Mage::getModel('SmartReport/Report')->getReportById($id))
                $reports[] = $r;
        }

        $reports = $this->sortReports($reports);

        return $reports;
    }


}