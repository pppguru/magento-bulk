<?php
class Extendware_EWCrawler_Block_Adminhtml_Job_Grid_Renderer_PercentCrawled extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	public function render(Varien_Object $job) {
		$percent = 0;
		$denominator = ($job->getNumGeneratedUrls() + $job->getNumLoggedUrls() + $job->getNumCustomUrls());
		if ($denominator > 0) {
			$percent = ($job->getNumCrawledUrls() / $denominator) * 100;
		}
		return round($percent) . '%';
	}
}