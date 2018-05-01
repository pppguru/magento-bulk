<?php
ini_set('display_errors', 0);


$path = realpath('');
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $filename)
{
		$files_name = $filename;
		$part = explode("/",$files_name);
		$last = end($part);
		if (!strstr($files_name,'..') && (strlen($last))>=3) {
			
			$paymentDate = date ("Y-m-d", filemtime($filename));
			$paymentDate=date('Y-m-d', strtotime($paymentDate));
			$contractDateBegin = date('Y-m-d', strtotime("25-05-2015"));
			$contractDateEnd = date('Y-m-d', strtotime("31-05-2015"));
		
			if (($paymentDate > $contractDateBegin) && ($paymentDate < $contractDateEnd))
			{
			  echo "$files_name was last modified: " . date ("Y-m-d", filemtime($files_name));
			  echo "<br />";
			} 
		}
}

?>
