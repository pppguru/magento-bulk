<?php

$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('datafeedmanager_configurations')} 
 ADD   `use_sftp` int(1) default '0',
 ADD `feed_taxonomy` varchar(200) default NULL,
 MODIFY  `datafeedmanager_attribute_sets` varchar(1500) default '*';
");

$installer->endSetup();
