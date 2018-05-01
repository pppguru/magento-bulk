<?php

Mage::helper('ewcore/cache')->clean();
$installer = $this;
$installer->startSetup();

$command = "";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(INTO\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(FROM\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

if ($command) $installer->run($command);
$installer->endSetup(); 

try {
	$incompatModules = array('Fooman_Speedster', 'GT_Speed');
	foreach ($incompatModules as $module) {
		$model = Mage::getSingleton('ewcore/module');
		if (!$model) continue;
		
		$module = $model->load($module);
		if ($module->isActive() === false) continue;
		
		Mage::getModel('compiler/process')->registerIncludePath(false);
		$configTools = Extendware::helper('ewcore/config_tools');
		if ($configTools) $configTools->disableModule($module);
	}
} catch (Exception $e) {}