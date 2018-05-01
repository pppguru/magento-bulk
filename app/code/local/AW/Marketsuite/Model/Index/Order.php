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


class AW_Marketsuite_Model_Index_Order extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('marketsuite/index_order');
    }

    public function processPage($ruleModel, $pageNumber)
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');

        $orderCollection
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_id')
            ->limitPage($pageNumber, AW_Marketsuite_Helper_Progress::PAGE_SIZE)
        ;
        $orderIdList = $ruleModel->getValidatedOrdersIds($orderCollection);
        $this->saveOrderListToIndex($ruleModel->getId(), $orderIdList);
    }

    /**
     * @param int   $ruleId
     * @param array $orderIdList
     */
    public function saveOrderListToIndex($ruleId, array $orderIdList = array())
    {
        $this->getResource()->saveOrderListToIndex($ruleId, $orderIdList);
    }

    public function clearIndexByRule($ruleModel)
    {
        $this->getResource()->clearIndexByRule($ruleModel);
    }

    public function updateOrder($orderId, $ruleId, $add)
    {
        $this->getResource()->updateOrder($orderId, $ruleId, $add);
    }
}