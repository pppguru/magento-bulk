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


if (@class_exists('Enterprise_Customer_Block_Form_Template')) {
    class AW_Mobile3_Block_Enterprise_Customer_Form_Template_Parent extends Enterprise_Customer_Block_Form_Template {}
} else {
    class AW_Mobile3_Block_Enterprise_Customer_Form_Template_Parent extends Mage_Core_Block_Template {
        protected function _toHtml()
        {
            return '';
        }

        public function addRenderer($type, $block, $template)
        {
            return $this;
        }
    }
}
class AW_Mobile3_Block_Enterprise_Customer_Form_Template extends AW_Mobile3_Block_Enterprise_Customer_Form_Template_Parent {}