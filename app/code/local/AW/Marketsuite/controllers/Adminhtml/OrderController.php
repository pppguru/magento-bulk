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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('marketsuite/orders');
    }

    public function indexAction()
    {
        $this->_title($this->__('MSS'))->_title($this->__('Orders'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('marketsuite');
        $this->renderLayout();
    }

    public function viewAction()
    {
        Mage::helper('marketsuite')->setBackUrl($this->getUrl('*/*/index'));
        return $this->_redirect(
            'adminhtml/sales_order/view',
            array(
                 'order_id'                                      => $this->getRequest()->getParam('order_id'),
                 AW_Marketsuite_Helper_Data::USE_AW_BACKURL_FLAG => 1,
            )
        );
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'orders.csv';
        $content = $this->getLayout()->createBlock('marketsuite/adminhtml_order_grid')->getCsv();
        $content = preg_replace("/(\s){2,}/", '/', $content);
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'orders.xml';
        $content = $this->getLayout()->createBlock('marketsuite/adminhtml_order_grid')->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
}