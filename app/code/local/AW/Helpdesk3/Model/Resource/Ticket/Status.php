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


class AW_Helpdesk3_Model_Resource_Ticket_Status extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_hdu3/ticket_status', 'id');
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_Status $status
     * @param $storeId
     *
     * @return string
     */
    public function getStatusTitle($status, $storeId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('tsl' => $this->getTable('aw_hdu3/ticket_status_label')), 'value')
            ->where('(tsl.store_id = ? OR tsl.store_id = 0)', $storeId)
            ->where('tsl.status_id = ?', $status->getId())
            ->where('tsl.value != ?', '')
            ->order('tsl.store_id ' . Zend_Db_Select::SQL_DESC)
            ->limit(1)
        ;
        return $read->fetchOne($select->__toString());
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_Status $status
     *
     * @return array
     */
    public function getLabelValues($status)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('tsl' => $this->getTable('aw_hdu3/ticket_status_label')),
                array('store_id' => 'tsl.store_id', 'value' => 'tsl.value')
            )
            ->where('tsl.status_id = ?', $status->getId())
            ->order('tsl.store_id ' . Zend_Db_Select::SQL_ASC)
        ;
        return $read->fetchPairs($select->__toString());
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param array                    $labelValues
     *
     * @return $this
     */
    public function setLabelValues(Mage_Core_Model_Abstract $object, $labelValues)
    {
        $write = $this->_getWriteAdapter();
        $where = $write->quoteInto('status_id =?', $object->getId());
        $write->delete($this->getTable('aw_hdu3/ticket_status_label'), $where);
        foreach ($labelValues as $storeId => $label) {
            $write->insert($this->getTable('aw_hdu3/ticket_status_label'),
                array(
                    'status_id' => $object->getId(),
                    'value'     => $label,
                    'store_id'  => $storeId,
                )
            );
        }
        return $this;
    }
}