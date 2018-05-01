<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Adminhtml_Statistic_WorkloadController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aw_hdu3/statistic/workload_report');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Statistic - Workload - Help Desk'));
        $this->loadLayout()
            ->_setActiveMenu('aw_hdu3')
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_redirect('*/*/view');
    }

    public function viewAction()
    {
        $this->_initAction()
            ->renderLayout()
        ;
    }

    public function exportAgentCsvAction()
    {
        $fileName = 'hdu3_workload_agents.csv';
        $content = $this->getLayout()->createBlock('aw_hdu3/adminhtml_statistic_workload_view_agent')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportAgentExcelXmlAction()
    {
        $fileName = 'hdu3_workload_agents.xml';
        $content = $this->getLayout()
            ->createBlock('aw_hdu3/adminhtml_statistic_workload_view_agent')
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportDepCsvAction()
    {
        $fileName = 'hdu3_workload_departments.csv';
        $content = $this->getLayout()->createBlock('aw_hdu3/adminhtml_statistic_workload_view_department')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportDepExcelXmlAction()
    {
        $fileName = 'hdu3_workload_departments.xml';
        $content = $this->getLayout()
            ->createBlock('aw_hdu3/adminhtml_statistic_workload_view_department')
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }
}