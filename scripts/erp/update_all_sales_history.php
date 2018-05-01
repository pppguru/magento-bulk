<?php
require_once(dirname(__FILE__).'/../../app/Mage.php');

Mage::reset();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$helper = mage::helper('AdvancedStock/Sales_History')->scheduleUpdateForAllProducts();

exit(1);