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


class AW_Advancedreports_SetupController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check access for current report
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        if ($secureKey = $this->getRequest()->getParam('sc')) {
            return Mage::getSingleton('admin/session')->isAllowed(base64_decode($secureKey));
        }
        $this->_redirectReferer();
        return false;
    }

    /**
     * Add Success message for future show
     *
     * @param string $message Message to show
     * @param mixed  $value   Value to show in string
     */
    protected function _addSuccess($message, $value = null)
    {
        $message = $value
            ? Mage::helper('advancedreports')->__($message, $value)
            : Mage::helper('advancedreports')->__(
                $message
            );
        Mage::getSingleton('core/session')->addSuccess($message);
    }

    /**
     * Add Error message for future show
     *
     * @param string $message Message to show
     * @param mixed  $value   Value to show in string
     */
    protected function _addError($message, $value = null)
    {
        $message = $value
            ? Mage::helper('advancedreports')->__($message, $value)
            : Mage::helper('advancedreports')->__(
                $message
            );
        Mage::getSingleton('core/session')->addError($message);
    }

    protected function _getActivatedMenu()
    {
        //TODO Activated menu
    }

    /**
     * Init Grid Container
     *
     * @return AW_Advancedreports_SetupController
     */
    protected function _initAction()
    {
        try {
            $this->_title($this->__('Reports'))
                ->_title($this->__('Advanced Reports'))
                ->_title($this->__('Customization'));
        } catch (Exception $e) {

        }

        $this->loadLayout()
            ->_setActiveMenu($this->_getActivatedMenu())
            ->_addBreadcrumb(
                Mage::helper('advancedreports')->__('Customization'),
                Mage::helper('advancedreports')->__('Customization')
            );

        return $this;
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

    /**
     * Edit Action
     */
    public function editAction()
    {
        if (($reportId = $this->getRequest()->getParam('report_id')) && ($adminId = $this->getSetup()->getAdminId())
        ) {
            $this->_initAction();

            # Store back to report url
            $this->getSetup()->setBackUrl($this->_getRefererUrl())->setReportsId($reportId);

            $options = Mage::getModel('advancedreports/option')->getCollection();
            $options
                ->addAdminIdFilter($adminId)
                ->addReportIdFilter($reportId);

            $this
                ->_addContent($this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit'))
                ->_addLeft($this->getLayout()->createBlock('advancedreports/adminhtml_setup_edit_tabs'));

            Mage::register('setup_data', $this->getSetup()->getPreparedData($options));
            $this->renderLayout();

        } else {
            $this->_addError('Report with defined id is not exists');
            $this->_redirectReferer();
        }
    }

    public function saveAction()
    {
        if (($post = $this->getRequest()->getPost()) && ($reportId = $this->getRequest()->getParam('report_id'))) {
            $backUrl = base64_decode($this->getRequest()->getParam('back_url'));
            try {
                $this->getSetup()->savePostData($post, $reportId);
                $this->_addSuccess('Customization successfully saved');
            } catch (Exception $e) {
                $this->_addError('Saving of customization was failed. Reason: %s', $e->getMessage());
                $this->_redirectReferer();
                return;
            }

            if ($this->getRequest()->getParam('back')) {
                $this->_redirectReferer();
            } else {
                $this->_redirectUrl($backUrl);
            }
            return;
        }
    }
}
