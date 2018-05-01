<?php
class Extendware_EWCrawler_Block_Adminhtml_Job_Edit_Tab_General extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
    protected function _prepareForm()
    {    	
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		
        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('General Information'),
        ));
      	
        $fieldset->addField('status', 'select', array(
        	'name'      => 'status',
			'label'     => $this->__('Status'),
        	'values'	=> $this->getJob()->getStatusOptionModel()->toFormSelectOptionArray(),
			'value'		=> $this->getJob()->getStatus() ? $this->getJob()->getStatus() : 'enabled',
        	'disabled' 	=> ($this->getJob()->getStatus() == 'disabled' or $this->getJob()->getState() == 'finished'),
			'note'		=> $this->__(''),
            'required'  => true,
        ));

                
        $fieldset->addField('state', 'label', array(
        	'name'      => 'state',
        	'value'		=> $this->getJob()->getStateLabel(),
            'label'     => $this->__('State'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('max_threads', 'text', array(
        	'name'      => 'max_threads',
        	'value'		=> $this->getJob()->getMaxThreads(),
            'label'     => $this->__('Max Threads'),
        	'note'		=> $this->__('The number of parallel requests performed at one time'),
        	'ewhelp'	=> $this->__('Raising this value too high will slow down your server. You can change this to determine the optimal value and then set it permanently in the crawler config.'),
            'disabled' 	=> ($this->getJob()->getStatus() == 'disabled' or $this->getJob()->getState() == 'finished'),
        	'bold'		=> true,
        ));
        
        $fieldset->addField('Crawled URLs', 'label', array(
        	'name'      => 'num_crawled_urls',
        	'value'		=> $this->getJob()->getNumCrawledUrls() !== null ? $this->getJob()->getNumCrawledUrls() : ' ---- ',
            'label'     => $this->__('Crawled URLs'),
        	'note'		=> $this->__('The number of URLs that have been crawled'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('Generated URLs', 'label', array(
        	'name'      => 'generated_urls',
        	'value'		=> $this->getJob()->getNumGeneratedUrls() !== null ? $this->getJob()->getNumGeneratedUrls() : ' ---- ',
            'label'     => $this->__('Generated URLs'),
        	'note'		=> $this->__('The number of URLs created through generation.'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('Logged URLs', 'label', array(
        	'name'      => 'logged_urls',
        	'value'		=> $this->getJob()->getNumLoggedUrls() !== null ? $this->getJob()->getNumLoggedUrls() : ' ---- ',
            'label'     => $this->__('Logged URLs'),
        	'note'		=> $this->__('The number of URLs created from logs. It can take a few weeks before this is > 0. Only available if you have the Deep Crawling / Logged Urls addon.'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('Custom URLs', 'label', array(
        	'name'      => 'custom_urls',
        	'value'		=> $this->getJob()->getNumCustomUrls() !== null ? $this->getJob()->getNumCustomUrls() : ' ---- ',
            'label'     => $this->__('Custom URLs'),
        	'note'		=> $this->__('The number of URLs that you have added programmatically according to the userguide.'),
            'bold'		=> true,
        ));
        
        if ($this->getJob()->getLastCrawlActivityAt()) {
	        $fieldset = $form->addFieldset('performance', array(
	        	'legend' => $this->__('Performance Information'),
	        ));
	        
	        $fieldset->addField('percent_crawled', 'label', array(
	        	'name'      => 'percent_crawled',
	        	'value'		=> round(($this->getJob()->getNumCrawledUrls() / ($this->getJob()->getNumGeneratedUrls() + $this->getJob()->getNumLoggedUrls() + $this->getJob()->getNumCustomUrls())) * 100) . '%',
	            'label'     => $this->__('Percent Crawled'),
	            'bold'		=> true,
	        ));
	        
	        $crawlFinished = $this->getJob()->getCrawlFinishedAt() ? strtotime($this->getJob()->getCrawlFinishedAt()) : strtotime($this->getJob()->getLastCrawlActivityAt());
	        $crawlTime = ($crawlFinished - strtotime($this->getJob()->getCrawlStartedAt()));
	        $crawlRate = @($this->getJob()->getNumCrawledUrls() / ($crawlTime/60));
	
	        $fieldset->addField('crawl_rate', 'label', array(
	        	'name'      => 'crawl_rate',
	        	'value'		=> sprintf('%.2d', $crawlRate),
	            'label'     => $this->__('Avg. Crawl Rate'),
	        	'note'		=> $this->__('The average number of URLs crawled per minute over the entire crawl'),
	            'bold'		=> true,
	        ));
		
	        $fieldset->addField('number_of_minutes', 'label', array(
	        	'name'      => 'number_of_minutes',
	        	'value'		=> round($crawlTime/60),
	            'label'     => $this->__('Number of Minutes'),
	        	'note'		=> $this->__('Total number of minutes that the crawl took'),
	            'bold'		=> true,
	        ));
        }
        
        $fieldset = $form->addFieldset('timing', array(
        	'legend' => $this->__('Timing Information'),
        ));
        
        $fieldset->addField('started_at', 'date_label', array(
        	'name'      => 'started_at',
        	'value'		=> $this->getJob()->getStartedAt(),
            'label'     => $this->__('Started'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('crawl_started_at', 'date_label', array(
        	'name'      => 'crawl_started_at',
        	'value'		=> $this->getJob()->getCrawlStartedAt(),
            'label'     => $this->__('Crawl Started'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('last_activity_at', 'date_label', array(
        	'name'      => 'last_activity_at',
        	'value'		=> $this->getJob()->getLastActivityAt(),
            'label'     => $this->__('Last Activity'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('last_crawl_activity_at', 'date_label', array(
        	'name'      => 'last_crawl_activity_at',
        	'value'		=> $this->getJob()->getLastCrawlActivityAt(),
            'label'     => $this->__('Last Crawl Activity'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('crawl_finished_at', 'date_label', array(
        	'name'      => 'crawl_finished_at',
        	'value'		=> $this->getJob()->getCrawlFinishedAt(),
            'label'     => $this->__('Crawl Finished'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('finished_at', 'date_label', array(
        	'name'      => 'finished_at',
        	'value'		=> $this->getJob()->getFinishedAt(),
            'label'     => $this->__('Finished'),
            'bold'		=> true,
        ));
        
        $fieldset->addField('scheduled_at', 'date_label', array(
        	'name'      => 'scheduled_at',
        	'value'		=> $this->getJob()->getScheduledAt(),
            'label'     => $this->__('Scheduled'),
            'bold'		=> true,
        ));
        
		$fieldset->addField('created_at', 'date_label', array(
        	'name'      => 'created_at',
        	'value'		=> $this->getJob()->getCreatedAt(),
            'label'     => $this->__('Created'),
            'bold'		=> true,
        ));

		$form->addValues($this->getAction()->getPersistentData('form_data', true));
        $form->addFieldNameSuffix('general');
		$form->setUseContainer(false);
        $this->setForm($form);
        
		return parent::_prepareForm();
	}
    
	public function getJob() {
        return Mage::registry('ew:current_job');
    }
}
