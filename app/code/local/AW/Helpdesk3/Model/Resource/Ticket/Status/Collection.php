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


class AW_Helpdesk3_Model_Resource_Ticket_Status_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/ticket_status');
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function joinLabelTable($storeId = 0)
    {
        $this->getSelect()
            ->joinLeft(
                array(
                    'tsl' => $this->getTable('aw_hdu3/ticket_status_label')
                ),
                'main_table.id = tsl.status_id AND tsl.store_id = ' . $storeId,
                array()
            )
            ->joinLeft(
                array(
                    'tsl_def' => $this->getTable('aw_hdu3/ticket_status_label')
                ),
                'main_table.id = tsl_def.status_id AND tsl_def.store_id = 0',
                array(
                    'store_id' => 'IF(tsl.store_id IS NULL, tsl_def.store_id, tsl.store_id)',
                    'value'    => 'IF(tsl.value IS NULL, tsl_def.value, IF(tsl.value = "", tsl_def.value, tsl.value))'
                )
            )
        ;
        return $this;
    }

    /**
     * @param $storeId
     *
     * @return $this
     */
    public function addLabelFilterByStoreId($storeId)
    {
        $this->addFieldToFilter('store_id', $storeId);
        return $this;
    }

    /**
     * @param string $valueField
     * @param string $labelField
     *
     * @return array
     */
    protected function _toOptionHash($valueField = 'id', $labelField = 'value')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this->addFieldToFilter('status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE);
    }

    /**
     * @return $this
     */
    public function addNotDeletedFilter()
    {
        return $this->addFieldToFilter('status',array('neq' => AW_Helpdesk3_Model_Source_Status::DELETED_VALUE));
    }
}