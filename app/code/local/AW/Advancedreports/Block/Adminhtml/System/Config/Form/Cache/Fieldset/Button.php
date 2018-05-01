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

/**
 * Button block
 */
class AW_Advancedreports_Block_Adminhtml_System_Config_Form_Cache_Fieldset_Button extends Mage_Core_Block_Template
{
    /**
     * Default button template
     */
    const DEFAULT_BUTTON_TEMPLATE = "advancedreports/fieldset/button.phtml";

    /**
     * This is constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::DEFAULT_BUTTON_TEMPLATE);
    }

    /**
     * Retrves ajax url for reset all Extra Downloads Statistics
     *
     * @return string
     */
    public function getResetAllUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl(
            'advancedreports_admin/aggregation/clean', array('_secure' => true)
        );
    }
}
