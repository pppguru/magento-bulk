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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Reply_Multiattach extends Varien_Data_Form_Element_File
{
    public function getHtmlAttributes()
    {
        return array('type', 'title', 'class', 'style', 'disabled', 'readonly', 'tabindex', 'multiple');
    }

    public function getElementHtml()
    {
        if ($checked = $this->getMultiple()) {
            $this->setData('multiple', true);
        } else {
            $this->unsetData('multiple');
        }
        return parent::getElementHtml() . $this->_getScriptHtml();
    }

    /**
     * @return string
     */
    public function _getScriptHtml()
    {
        $params = array(
            'fileListClassName' => 'awhdu3-ticketform-file-list',
            'errorMsgClassName' => 'awhdu3-ticketform-file-list-error-el',
            'fileListElName'    => 'attachment_needed',
        );
        $html = "<script type='text/javascript'>";
        $html .= "new AWLIB.FileUploader($('" . $this->getId() . "'), " . Zend_Json::encode($params) . ");";
        $html .= "</script>";
        return $html;
    }
}