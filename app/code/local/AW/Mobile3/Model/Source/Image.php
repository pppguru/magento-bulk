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


if(@class_exists('Mage_Adminhtml_Model_System_Config_Backend_File')){
    class AW_Mobile3_Model_Source_ImageCommon extends Mage_Adminhtml_Model_System_Config_Backend_File
    {
    }
} else {
    class AW_Mobile3_Model_Source_ImageCommon extends Mage_Adminhtml_Model_System_Config_Backend_Image
    {
    }
}

class AW_Mobile3_Model_Source_Image extends AW_Mobile3_Model_Source_ImageCommon
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return array
     */
    protected function _getAllowedExtensions()
    {
        return array('jpg', 'jpeg', 'gif', 'png', 'svg');
    }
}