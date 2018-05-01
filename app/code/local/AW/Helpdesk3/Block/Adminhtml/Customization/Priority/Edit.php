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


class AW_Helpdesk3_Block_Adminhtml_Customization_Priority_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'aw_hdu3';
        $this->_controller = 'adminhtml_customization_priority';
        $this->_formScripts[] = "
            function saveAndContinueEdit(url) {
               editForm.submit(url);
            }
        ";
        parent::__construct();
    }

    public function getHeaderText()
    {
        $title = $this->__('New Ticket Priority');
        /** @var AW_Helpdesk3_Model_Ticket_Priority $priority */
        $priority = Mage::registry('current_priority');
        if (null !== $priority->getId()) {
            $title = $this->__('Edit Ticket Priority "%s"', $priority->getTitle(Mage::app()->getStore()->getId()));
            if ($priority->getIsSystem()) {
                $this->_updateButton('delete', 'disabled', true);
                $this->_updateButton('delete', 'title', $this->__('System Priority cannot be disabled or deleted'));
            }
        }
        return $title;
    }

    public function getHeaderCssClass()
    {
        return 'head-' . strtr($this->_controller, '_', '-');
    }

    protected function _prepareLayout()
    {
        $this->_addButton(
            'save_and_continue',
            array(
                'label'   => $this->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class'   => 'save'
            ), 10
        );
        parent::_prepareLayout();
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            array(
                '_current' => true,
                'continue' => 1,
            )
        );
    }
}
