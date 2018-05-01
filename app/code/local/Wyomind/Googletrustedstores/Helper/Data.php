<?php

class Wyomind_Googletrustedstores_Helper_Data extends Mage_Core_Helper_Data {

    public $carrier_code = array(
        "UPS" => "UPS",
        "USPS" => "USPS",
        "FedEx" => "FedEx"
    );
    public $other_carrier_code = array(
        "ABF Freight Systems" => "ABFS",
        "America West" => "AMWST",
        "Bekins" => "BEKINS",
        "Conway" => "CNWY",
        "DHL" => "DHL",
        "Estes" => "ESTES",
        "Home Direct USA" => "HDUSA",
        "LaserShip" => "LASERSHIP",
        "Mayflower" => "MYFLWR",
        "Old Dominion Freight" => "ODFL",
        "Reddaway" => "RDAWAY",
        "Team Worldwide" => "TWW",
        "Watkins" => "WATKINS",
        "Yellow Freight" => "YELL",
        "YRC" => "YRC",
    );
    
    public function isCarrier($code) {
        return in_array($code, array_values($this->carrier_code));
    }
    public function isOther($code) {
        return in_array($code, array_values($this->other_carrier_code));
    }

}
