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


class AW_Helpdesk3_Model_Source_Ticket_Priority
{
    const URGENT_VALUE    = 1;
    const ASAP_VALUE      = 2;
    const TODO_VALUE      = 3;
    const IF_TIME_VALUE   = 4;

    /**
     * @param int $storeId
     *
     * @return array
     */
    static public function toOptionArray($storeId = 0)
    {
        $priorityCollection = Mage::getModel('aw_hdu3/ticket_priority')->getCollection();
        $priorityCollection
            ->joinLabelTable($storeId)
            ->addActiveFilter()
        ;
        $options = array();
        foreach ($priorityCollection->toOptionHash() as $value => $label) {
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
        $priorityCollection = self::_getLabeledCollection($storeId);
        $priorityCollection->addActiveFilter();
        return $priorityCollection->toOptionHash();
    }

    static public function toOptionHashExpanded($storeId = 0)
    {
        $priorityCollection = self::_getLabeledCollection($storeId);
        $priorityCollection->addNotDeletedFilter();
        return $priorityCollection->toOptionHash();
    }

    static protected function _getLabeledCollection($storeId = 0) {
        $priorityCollection = Mage::getModel('aw_hdu3/ticket_priority')->getCollection();
        $priorityCollection->joinLabelTable($storeId);
        return $priorityCollection;
    }

}
