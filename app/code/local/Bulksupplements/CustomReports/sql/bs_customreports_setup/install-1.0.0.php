<?php
$installer = $this;
$installer->startSetup();
$installer->run("DELETE FROM core_resource WHERE code='reportkilogram_setup'");
$installer->endSetup();