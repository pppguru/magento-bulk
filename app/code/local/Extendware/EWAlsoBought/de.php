<?php /* @copyright   Copyright (c) 2014 Extendware. (http://www.extendware.com) */ ?>
<?php if (isset($_SERVER['REQUEST_METHOD'])): ?>
	<h1>Extendware Core Installation Error</h1>
	<h2>Missing IonCube Dependency</h2>
	<p>IonCube needs to be installed on this server. Please follow the instructions in the Extendware Core <i>installation.html</i> file under the heading <u>Checking for dependencies</u> and the <i>ioncube.html</i> file that was included in the Extendware Core package.
	IonCube is free, easy to install, and usually your hosting provider will install it on your behalf if you do not have the expertise to install it. If you need installation help we also offer <a href="http://www.extendware.com/services/magento-extension-installation.html" target="_blank">installation services</a>.</p>
	
	<h2>Restoring Your Site</h2>
	<p>Until IonCube is installed you can restore your site following the following instructions:</p>
	<ol>
		<li>Delete all "Extendware" prefixed files in <i>[Magento root]/app/etc</i></li>
		<li>Delete all "Extendware" prefixed files in <i>[Magento root]/app/etc/modules</i></li>
		<li>Delete the file at <i>[Magento root]/app/code/local/Varien/Autoload.php</i>
	</ol>
	<p><b>Note: </b> If the site is not restored after following these steps you will need to flush your Magento cache and ensure system compilation is disabled.</p>
<?php else: ?>
IonCube needs to be installed on the command line. Even if it is installed on the Web server it is not installed on the PHP used to execute this script. To resolve this issue you should contact your host to install IonCube for this PHP instance.

Other Possible Workarounds:

1) Call this php script using "php -c [path to custom ini file] -f [path to script]" where [path to custom ini file] is the ini file used by your web server where IonCube is installed. If the PHP versions are the same, then this will work.

2) Call this script via the web server using the following command "wget [url to script] > /dev/null 2>&1". For example: wget http://www.yourdomain.com/cron.php > /dev/null 2>&1
<?php exit; ?>
<?php endif; ?>
<?php die('IonCube is required to be installed. Please contact your hosting provider'); ?>