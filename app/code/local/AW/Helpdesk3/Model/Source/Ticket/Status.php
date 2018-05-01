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


class AW_Helpdesk3_Model_Source_Ticket_Status
{
    const NEW_VALUE     = 1;
    const OPEN_VALUE    = 2;
    const CLOSED_VALUE  = 3;
    const WAITING_VALUE = 4;

    const NEW_AND_OPEN_VALUE = 0;

    /**
     * @param int $storeId
     *
     * @return array
     */
    static public function toOptionArray($storeId = 0)
    {
        $statusCollection = Mage::getModel('aw_hdu3/ticket_status')->getCollection();
        $statusCollection
            ->joinLabelTable($storeId)
            ->addActiveFilter()
        ;
        $options = array();
        foreach ($statusCollection->toOptionHash() as $value => $label) {
            $options[] = array('value' => $value, 'label' => $label);
        }
        return $options;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    static public function toOptionHash($storeId = 0)
    {
        $statusCollection = self::_getLabeledCollection($storeId);
        $statusCollection->addActiveFilter();
        return $statusCollection->toOptionHash();
    }

    static public function getNewAndOpenStatusToOptionHash()
    {
        return array(self::NEW_AND_OPEN_VALUE => Mage::helper('aw_hdu3')->__('New and Open'));
    }

    static public function toOptionHashExpanded($storeId = 0)
    {
        $priorityCollection = self::_getLabeledCollection($storeId);
        $priorityCollection->addNotDeletedFilter();
        return $priorityCollection->toOptionHash();
    }

    static protected function _getLabeledCollection($storeId = 0) {
        $priorityCollection = Mage::getModel('aw_hdu3/ticket_status')->getCollection();
        $priorityCollection->joinLabelTable($storeId);
        return $priorityCollection;
    }

}
