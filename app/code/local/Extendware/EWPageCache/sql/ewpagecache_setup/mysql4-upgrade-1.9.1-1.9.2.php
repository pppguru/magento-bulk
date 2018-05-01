<?php

$installer = $this;
$installer->startSetup();
$installer->endSetup();

Mage::helper('ewpagecache/config')->autoConfigure();
Mage::helper('ewpagecache/config')->reload();