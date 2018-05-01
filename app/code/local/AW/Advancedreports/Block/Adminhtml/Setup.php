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
 * Setup Block
 */
class AW_Advancedreports_Block_Adminhtml_Setup extends Mage_Adminhtml_Block_Abstract
{
    const DATA_KEY_SECURE_CHECK = 'aw_ar_secure_check';
    const DATA_KEY_REPORT_TITLE = 'aw_ar_report_title';
    const DATA_KEY_REPORT_ROUTE = 'aw_ar_report_route';

    /**
     * Retrieves Setup Instance
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSetup()
    {
        return $this->getData('setup');
    }

    /**
     * Retrieves Setup Url
     *
     * @return string
     */
    public function getSetupUrl()
    {
        return $this->getUrl(
            'advancedreports_admin/setup/edit',
            array(
                'report_id' => $this->getSetup()->getReportId(),
                'sc'        => base64_encode(Mage::registry(self::DATA_KEY_SECURE_CHECK)),
                'title'     => base64_encode(Mage::registry(self::DATA_KEY_REPORT_TITLE)),
                'route'     => base64_encode(Mage::registry(self::DATA_KEY_REPORT_ROUTE)),
            )
        );
    }
}
