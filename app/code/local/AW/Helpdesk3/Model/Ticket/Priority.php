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


/**
 * Class AW_Helpdesk3_Model_Ticket_Priority
 * @method string getId()
 * @method string getStatus()
 * @method string getFontColor()
 * @method string getBackgroundColor()
 * @method string getIsSystem()
 */
class AW_Helpdesk3_Model_Ticket_Priority extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/ticket_priority');
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getTitle($storeId = 0)
    {
        return $this->getResource()->getPriorityTitle($this, $storeId);
    }

    /**
     * @return array
     */
    public function getLabelValues()
    {
        if (null === $this->getData('label_values')) {
            $this->setData('label_values', $this->getResource()->getLabelValues($this));
        }
        return $this->getData('label_values');
    }

    /**
     * @param array $labelValues
     *
     * @return AW_Helpdesk3_Model_Resource_Ticket_Priority
     */
    public function setLabelValues($labelValues)
    {
        return $this->getResource()->setLabelValues($this, $labelValues);
    }

    public function isEnabled() {
        return ($this->getStatus() == AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE);
    }

}
