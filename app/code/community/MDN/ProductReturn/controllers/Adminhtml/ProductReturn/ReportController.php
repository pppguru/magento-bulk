<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author     : Sylvain SALERNO
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Adminhtml_ProductReturn_ReportController extends Mage_Adminhtml_Controller_Action
{

    public function ReasonAction()
    {

        if ($this->getRequest()->getPost('csv') == 1) {
            //csv download
            $block    = $this->getLayout()->createBlock('ProductReturn/Report_Reason_Results');
            $fileName = 'rma_reasons_from_' . $block->_from . '_to_' . $block->_to . '_groupby_' . $block->_groupBy . '.csv';
            $content  = $block->getCsv();
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            //normal display
            $this->loadLayout();
            $this->renderLayout();
        }

    }

    /**
     * show (or download csv report)
     */
    public function ProductAction()
    {

        if ($this->getRequest()->getPost('csv') == 1) {
            //csv download
            $block    = $this->getLayout()->createBlock('ProductReturn/Report_Product_Results');
            $fileName = 'rma_reasons_from_' . $block->_from . '_to_' . $block->_to . '_groupby_' . $block->_groupBy . '.csv';
            $content  = $block->getCsv();
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            //normal display
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    /**
     * Product report grid in ajax
     */
    public function productGridAjaxAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('ProductReturn/Report_Product_Results');

        $block->_from    = date('Y-m-d', strtotime($this->getRequest()->getParam('from')));
        $block->_to      = date('Y-m-d', strtotime($this->getRequest()->getParam('to')));
        $block->_groupBy = $this->getRequest()->getParam('group_by');

        $this->getResponse()->setBody($block->toHtml());

    }


    protected function _isAllowed()
    {
        return true;
    }

}