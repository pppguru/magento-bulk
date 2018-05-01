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
 * Advanced Report Action Controller
 */
class AW_Advancedreports_Controller_Action extends Mage_Adminhtml_Controller_Action
{
    /**
     * Helper
     *
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    protected function  _setActiveMenu($menuPath)
    {
        parent::_setActiveMenu($menuPath);
        /**
         *  Save current report access key for limitaton of Custom Options dialog
         *
         * @see AW_Advancedreports_Block_Adminhtml_Setup
         */
        Mage::register(AW_Advancedreports_Block_Adminhtml_Setup::DATA_KEY_SECURE_CHECK, $menuPath, true);
        return $this;
    }

    protected function _setSetupTitle($name)
    {
        Mage::register(AW_Advancedreports_Block_Adminhtml_Setup::DATA_KEY_REPORT_TITLE, $name, true);
        try {
            $this->_title($this->__('Reports'))
                ->_title($this->__('Advanced Reports'))
                ->_title($name);

        } catch (Exception $e) {

        }
        return $this;
    }

    /**
     * Response for Ajax Request
     *
     * @param array $result
     */
    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
}
