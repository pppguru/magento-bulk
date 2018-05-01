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


class AW_Advancedreports_ProductController extends AW_Advancedreports_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/advancedreports/product');
    }

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/advancedreports/product')
            ->_setSetupTitle(Mage::helper('advancedreports')->__('Sales by Product'))
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Advanced'), Mage::helper('advancedreports')->__('Advanced')
            )
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Sales by Product'),
                Mage::helper('advancedreports')->__('Sales by Product')
            )
            ->_addContent($this->getLayout()->createBlock('advancedreports/advanced_product'))
            ->renderLayout();
    }

    public function exportOrderedCsvAction()
    {
        $fileName = 'product.csv';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_product_grid')
            ->setIsExport(true)
            ->getCsv()
        ;

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportOrderedExcelAction()
    {
        $fileName = 'product.xml';
        $content = $this->getLayout()
            ->createBlock('advancedreports/advanced_product_grid')
            ->setIsExport(true)
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _getProductCollection()
    {
        return Mage::getModel('catalog/product')->getCollection();
    }

    public function getSkuAction()
    {
        if ($sku = $this->getRequest()->getParam('sku')) {
            Mage::register(AW_Advancedreports_Helper_Setup::DATA_KEY_REPORT_ID, 'gridProduct');
            $limit = $this->getRequest()->getParam('limit');
            $sku = base64_decode($sku);
            $sku = $this->escapeHtml($sku);
            $sku = str_replace("&amp;", "&", $sku);
            $collection = $this->_getProductCollection();
            $collection->setPageSize($limit)
                ->setCurPage($this->getRequest()->getParam('p') ? $this->getRequest()->getParam('p') : 0);
            $collection->addFieldToFilter('sku', array('like' => "%{$sku}%"));
            $skuTable = $collection->getResource()->getTable('advancedreports/sku');
            $collection->getSelect()
                ->joinLeft(array('skuRel' => $skuTable), "skuRel.sku = e.sku", array())
                ->order("IFNULL(`skuRel`.`relevance`, 0) DESC")
                ->order('sku ASC');

            $collection->setPageSize($limit);

            $skus = array();
            foreach ($collection as $product) {
                $skus[] = $product->getSku();
            }

            $this->_ajaxResponse(
                array(
                     'count' => count($skus),
                     'sku'   => $sku,
                     'skus'  => $skus
                )
            );
            return;

        }
        $this->_ajaxResponse(array('count' => 0));
    }

    public function storeSkuAction()
    {
        if ($sku = $this->getRequest()->getParam('sku')) {
            $sku = base64_decode($sku);
            $skuRel = Mage::getModel('advancedreports/sku')->load($sku, 'sku');
            $skuRel->incRelevance($sku)->save();
        }
    }

    /**
     * Escape html entities
     *
     * @param   mixed $data
     * @param   array $allowedTags
     *
     * @return  mixed
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);
                    $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                    $result = htmlspecialchars($result);
                    $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }
}
