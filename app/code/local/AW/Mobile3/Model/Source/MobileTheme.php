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
 * @package    AW_Mobile3
 * @version    3.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Mobile3_Model_Source_MobileTheme
{
    public function toOptionArray()
    {
        $package = AW_Mobile3_Helper_Data::IPHONE_PACKAGE_NAME;
        $themes = array_diff(
            Mage::getDesign()->getThemeList($package),
            array(AW_Mobile3_Helper_Data::IPAD_THEME_NAME)
        );
        $defaultTheme = Mage::getDesign()->getDefaultTheme();
        if (in_array($defaultTheme, $themes)) {
            array_unshift($themes, $defaultTheme);
            $themes = array_unique($themes);
        }
        $options = array(array('label' => $package, 'value' => array()));
        foreach ($themes as $theme) {
            $options[0]['value'][] = array(
                'label' => $theme,
                'value' => $theme
            );
        }
        return $options;
    }
}