<?php
/**
 * @author Amasty Team
 * @copyright Amasty
 * @package Amasty_Fpc
 */

class Amasty_Fpc_Model_Resource_Stats extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('amfpc/stats', 'id');
    }
}