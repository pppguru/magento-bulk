<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$installer->removeAttribute('catalog_product', 'rpsc_countries');
$installer->removeAttribute('catalog_product', 'rpsc_access_control');
$installer->endSetup();
