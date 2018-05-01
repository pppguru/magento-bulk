<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Adminhtml_Etipsecurity_LogController
 */
class ET_IpSecurity_Adminhtml_Etipsecurity_LogController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action
     * @return ET_IpSecurity_Adminhtml_Etipsecurity_LogController $this
     */
    protected function _initAction()
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');

        $this->loadLayout()->_setActiveMenu('customers')->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Customers'),
            $helper->__('ET IP Security log')
        );

        return $this;
    }

    /**
     * Default Action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Export grid data to csv file Action
     */
    public function exportCsvAction()
    {
        $fileName = 'et_ipsecurity.csv';

        /** @var ET_IpSecurity_Block_Adminhtml_Log_Grid $block */
        $block = $this->getLayout()->createBlock('etipsecurity/adminhtml_log_grid');
        $content = $block->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export grid data to xml file Action
     */
    public function exportXmlAction()
    {
        $fileName = 'et_ipsecurity.xml';

        /** @var ET_IpSecurity_Block_Adminhtml_Log_Grid $block */
        $block = $this->getLayout()->createBlock('etipsecurity/adminhtml_log_grid');
        $content = $block->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * @param string $fileName
     * @param string $content
     * @param string $contentType
     */
    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    /**
     * Check for ACL permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/etipsecurity');
    }
}