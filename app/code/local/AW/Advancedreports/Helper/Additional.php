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


class AW_Advancedreports_Helper_Additional extends AW_Advancedreports_Helper_Abstract
{
    const REGISTRY_PATH = 'aw_advancedreports_additional';

    /**
     * Returns reports factory class
     *
     * @return AW_Advancedreports_Model_Additional_Reports
     */
    public function getReports()
    {
        return Mage::getSingleton('advancedreports/additional_reports');
    }

    /**
     * Item name
     *
     * @param $name
     *
     * @return AW_Advancedreports_Model_Additional_Item
     */
    protected function _getItemByName($name)
    {
        $reports = $this->getReports()->getReports();
        if (is_array($reports) || $reports instanceof Traversable) {
            foreach ($this->getReports()->getReports() as $report) {
                if ($report->getName() == $name) {
                    return $report;
                }
            }
        }
        return new AW_Advancedreports_Model_Additional_Item();
    }

    public function getVersionCheck($item)
    {
        if (is_string($item)) {
            return version_compare(
                $this->_helper()->getVersion(), $this->_getItemByName($item)->getRequiredVersion(), '>='
            );
        } elseif ($item instanceof AW_Advancedreports_Model_Additional_Item) {
            return version_compare($this->_helper()->getVersion(), $item->getRequiredVersion(), '>=');
        }
        return null;
    }
}
