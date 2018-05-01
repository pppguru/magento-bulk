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
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Catalogpermissions_Model_Mysql4_Cache extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('catalogpermissions/cache', 'id');
    }

    public function loadOrderItemIds($dealId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('collpur/dealpurchases'), array('order_item_id'))
            ->where('deal_id=?', $dealId);
        return $this->_getReadAdapter()->fetchCol($select);
    }

}
