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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Customer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

    protected $_cacheData = null;


    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /** @var AW_Helpdesk3_Model_Ticket $row */
        $customerId = $this->_getCustomerIdByEmail($row->getCustomerEmail());
        if (null !== $customerId) {
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $customerId));
            return "<a href='{$url}'>" . parent::render($row) . "</a>";
        }
        return parent::render($row);
    }

    /**
     * @param string $email
     *
     * @return null|int
     */
    protected function _getCustomerIdByEmail($email)
    {
        if (null === $this->_cacheData) {
            $customerEmails = $this->getColumn()->getGrid()->getCollection()->getColumnValues('customer_email');
            $this->_cacheData = Mage::helper('aw_hdu3/ticket')->getCustomerIdsByCustomerEmails($customerEmails);
        }
        if (array_key_exists($email, $this->_cacheData)) {
            return $this->_cacheData[$email];
        }
        return null;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $result = parent::renderExport($row);
        return strip_tags($result);
    }
}