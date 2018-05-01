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


class AW_Advancedreports_Additional_ReportController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/' . $this->_getName());
    }

    protected function _initAddName()
    {
        if (!Mage::registry('aw_advancedreports_additional_name') && $this->getRequest()->getParam('name')) {
            Mage::register('aw_advancedreports_additional_name', $this->getRequest()->getParam('name'));
        }
        return $this;
    }

    protected function _getName()
    {
        $this->_initAddName();
        return Mage::registry('aw_advancedreports_additional_name');
    }

    /**
     * Is unit comparable with current reports
     *
     * @param string $name
     *
     * @return bool
     */
    protected function _isComparable($name)
    {
        return $this->_helper()->getAdditional()->getVersionCheck($name);
    }

    public function indexAction()
    {
        if ($this->_isComparable($this->_getName())) {
            $this->loadLayout()
                ->_setSetupTitle(
                    Mage::helper('advancedreports')->__(
                        Mage::helper('advancedreports/additional')->getReports()->getTitle($this->_getName())
                    )
                )
                ->_setActiveMenu('report/advancedreports/' . $this->_getName())
                ->_addBreadcrumb(
                    Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
                )
                ->_addBreadcrumb(
                    Mage::helper('advancedreports')->__(
                        Mage::helper('advancedreports/additional')->getReports()->getTitle($this->_getName())
                    ),
                    Mage::helper('advancedreports')->__(
                        Mage::helper('advancedreports/additional')->getReports()->getTitle($this->_getName())
                    )
                )
                ->_addContent($this->getLayout()->createBlock('advancedreports/additional_' . $this->_getName()))
                ->renderLayout()
            ;
        } else {
            Mage::getSingleton('core/session')->addError(
                $this->_helper()->__(
                    'This version of the Unit is not comparable with the current version of Advanced Reports'
                )
            );
            $this->_redirectReferer();
        }
    }

    public function exportOrderedCsvAction()
    {
        $this->_initAddName();
        $fileName = $this->_getName() . '.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/additional_' . $this->_getName() . '_grid')
            ->setIsExport(true)
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $this->_initAddName();
        $fileName = $this->_getName() . '.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/additional_' . $this->_getName() . '_grid')
            ->setIsExport(true)
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function gridAction()
    {
        $this->_initAddName();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('advancedreports/additional_' . $this->_getName() . '_grid')->toHtml()
        );
    }
}
