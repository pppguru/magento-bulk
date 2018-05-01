<?php
list($script, $basePath, $type, $compressor, $inFile, $outFile) = $argv;

require $basePath .  '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);

if (file_exists($inFile) === false) {
	Mage::helper('ewminify/system')->log(Mage::helper('ewminify')->__('File does not exists: %s', $inFile));
	exit;
}

$contents = file_get_contents($inFile);

Extendware_EWMinify_Model_Minify::setDisableExternalMinifier(true);

$minifiedContents = null;
if ($type == 'js') {
	$minifiedContents = Extendware_EWMinify_Model_Minify::js($contents, array(), $compressor);
} elseif ($type == 'css') {
	$minifiedContents = Extendware_EWMinify_Model_Minify::css($contents, array(), $compressor);
}

if ($minifiedContents !== null) {
	if (file_put_contents($outFile, $minifiedContents, LOCK_EX) !== false) {
		echo 'OK';
	} else {
		@unlink($outFile);
	}
}