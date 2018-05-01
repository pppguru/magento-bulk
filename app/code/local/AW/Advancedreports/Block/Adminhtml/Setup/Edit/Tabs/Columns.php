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


class AW_Advancedreports_Block_Adminhtml_Setup_Edit_Tabs_Columns extends Mage_Adminhtml_Block_Widget_Form
{
    const TEMPLATE_PATH = 'advancedreports/setup/tabs/columns.phtml';

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    /**
     * Retrieves setup
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return Mage::helper('advancedreports/setup');
    }

    public function getColumns()
    {
        if ($grid = $this->getSetup()->getGrid()) {
            return $grid->getSetupColumns();
        }
        return null;
    }

    /**
     * Retribes serialized custom columns
     *
     * @return string
     */
    public function getValue()
    {
        return $this->getSetup()->getGrid()->getCustomOption('custom_columns');
    }
}
