<?php

class MDN_CompetitorPrice_Model_System_Config_Source_GoogleShoppingCountry extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    protected $_options;

    public function toOptionArray() {

        if (!$this->_options) {
            $this->getAllOptions();
        }
        return $this->_options;
    }

    //todo : use webservice instead
    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array();
            $this->_options[] = array( 'value' => '', 'label' => '');
            $channels = $this->getChannelsFromApi();
            $alreadyAdded = array();
            foreach($channels as $channel) {
                list($organization, $locale) = explode('_', $channel);
                if (!in_array($channel, $alreadyAdded))
                    $this->_options[] = array( 'value' => $channel, 'label' => $organization.'.'.$locale);
                $alreadyAdded[] = $channel;
            }
        }
        return $this->_options;
    }

    private function getChannelsFromApi()
    {
        $response = file_get_contents(Mage::getStoreConfig('competitorprice/general/host').'/channels');

        $response = json_decode($response);

        return $response->body;
    }

}