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


class AW_Helpdesk3_Model_Resource_Ticket_Priority extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_hdu3/ticket_priority', 'id');
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_Priority $priority
     * @param $storeId
     *
     * @return string
     */
    public function getPriorityTitle($priority, $storeId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('tpl' => $this->getTable('aw_hdu3/ticket_priority_label')), 'value')
            ->where('(tpl.store_id = ? OR tpl.store_id = 0)', $storeId)
            ->where('tpl.priority_id = ?', $priority->getId())
            ->where('tpl.value != ?', '')
            ->order('tpl.store_id ' . Zend_Db_Select::SQL_DESC)
            ->limit(1)
        ;
        return $read->fetchOne($select->__toString());
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_Priority $priority
     *
     * @return array
     */
    public function getLabelValues($priority)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('tpl' => $this->getTable('aw_hdu3/ticket_priority_label')),
                array('store_id' => 'tpl.store_id', 'value' => 'tpl.value')
            )
            ->where('tpl.priority_id = ?', $priority->getId())
            ->order('tpl.store_id ' . Zend_Db_Select::SQL_ASC)
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
        $where = $write->quoteInto('priority_id =?', $object->getId());
        $write->delete($this->getTable('aw_hdu3/ticket_priority_label'), $where);
        foreach ($labelValues as $storeId => $label) {
            $write->insert($this->getTable('aw_hdu3/ticket_priority_label'),
                array(
                    'priority_id' => $object->getId(),
                    'value'     => $label,
                    'store_id'  => $storeId,
                )
            );
        }
        return $this;
    }
}