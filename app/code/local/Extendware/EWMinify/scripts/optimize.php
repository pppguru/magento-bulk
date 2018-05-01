<?php
list($script, $basePath, $file) = $argv;

require $basePath .  '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);

$verboseLogging = Mage::helper('ewminify/config')->isVerboseLogEnabled();

$copiedFile = dirname($file) . DS . '__' . mt_rand(1, 9999) . basename($file);
if (@copy($file, $copiedFile) === false) {
	if ($verboseLogging) Mage::helper('ewminify/system')->log(Mage::helper('ewminify/system')->__('Could not copy %s to %s', $file, $copiedFile));
	exit;
}

Mage::helper('ewminify')->optimizeImage($copiedFile, false);

if (filesize($copiedFile) and filesize($copiedFile) < filesize($file)) {
	if (@rename($copiedFile, $file) === false) {
		@file_put_contents($file, file_get_contents($copiedFile));
	}
}

@unlink($copiedFile);