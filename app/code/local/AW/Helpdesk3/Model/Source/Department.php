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


class AW_Helpdesk3_Model_Source_Department
{
    static public function toOptionArray($excludeDepId = null)
    {
        $options = array();
        if(!Mage::helper('aw_hdu3/config')->isPrimaryDepartmentActive()) {
            $options[]=array('value' => '', 'label' => Mage::helper('aw_hdu3')->__('--Please select--'));
        }
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
        $departmentCollection
            ->sortByOrder()
            ->addActiveFilter()
        ;
        foreach ($departmentCollection->toOptionHash() as $value => $label) {
            if ($excludeDepId != $value) {
                $options[] = array('value' => $value, 'label' => $label);
            }
        }
        return $options;
    }

    /**
     * @param null|int $storeId
     *
     * @return array
     */
    static public function toOptionArrayForStoreId($storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $options = array(array('value' => '', 'label' => Mage::helper('aw_hdu3')->__('--Please select--')));
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
        $departmentCollection
            ->addFilterByStoreId($storeId)
            ->sortByOrder()
            ->addActiveFilter()
        ;
        foreach ($departmentCollection->toOptionHash() as $value => $label) {
            $options[] = array('value' => $value, 'label' => $label);
        }
        return $options;
    }
}