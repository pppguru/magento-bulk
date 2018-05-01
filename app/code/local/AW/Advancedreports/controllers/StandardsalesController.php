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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_StandardsalesController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/standardsales');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/standardsales')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(Mage::helper('advancedreports')->__('Sales'), Mage::helper('advancedreports')->__('Sales'))
            ->_addContent($this->getLayout()->createBlock('advancedreports/report_sales_sales'))
            ->renderLayout();
    }

    public function exportStandardsalesCsvAction()
    {
        $fileName = 'standardsales.csv';
        $content = $this->getLayout()->createBlock('advancedreports/report_sales_sales_grid')->setIsExport(true)
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportStandardsalesExcelAction()
    {
        $fileName = 'standardsales.xml';
        $content = $this->getLayout()->createBlock('advancedreports/report_sales_sales_grid')->setIsExport(true)
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }
}
