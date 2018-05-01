<?php

$installer = $this;

$installer->startSetup();

if (!(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
    $installer->run("UPDATE {$this->getTable('dataflow_profile')} set entity_type = NULL WHERE name = 'import product packaging';");
}

$installer->endSetup();


