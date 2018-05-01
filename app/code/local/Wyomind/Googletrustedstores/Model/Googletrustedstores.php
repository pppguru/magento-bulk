<?php class Wyomind_Googletrustedstores_Model_Googletrustedstores extends Mage_Core_Model_Abstract {
    var $SEP = "	";
    var $NEWLINE = "
";
    var $HEADERS_s = "merchant order id	tracking number	carrier code	other carrier name	ship date
";
    var $HEADERS_c = "merchant order id	reason
";
    var $x4b = null;
    var $x4c = null;
    public function __construct() {
        $x6d = "str_replace";
        $x6e = "trim";
        $x6f = "strtotime";
        $x70 = "dirname";
        $x71 = "json_decode";
        $x72 = "count";
        $this->last_update = Mage::getStoreConfig("googletrustedstores/schedule/last_update");
        $this->cron_expr = Mage::getStoreConfig("googletrustedstores/schedule/cron");
    }
    public function getLastUpdate() {
        $x6d = "str_replace";
        $x6e = "trim";
        $x6f = "strtotime";
        $x70 = "dirname";
        $x71 = "json_decode";
        $x72 = "count";
        if ($this->last_update == null || $this->last_update == 0) {
            $this->last_update = Mage::getSingleton("core/date")->date("Y-m-d H:i:s");
        }
        return $this->last_update;
    }
    public function setLastUpdate() {
        $x6d = "str_replace";
        $x6e = "trim";
        $x6f = "strtotime";
        $x70 = "dirname";
        $x71 = "json_decode";
        $x72 = "count";
        $this->last_update = Mage::getSingleton("core/date")->gmtDate("Y-m-d H:i:s");
        Mage::getConfig()->saveConfig("googletrustedstores/schedule/last_update", $this->last_update, "default", "0");
        Mage::getConfig()->cleanCache();
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }
    public function getCronExpr() {
        $x6d = "str_replace";
        $x6e = "trim";
        $x6f = "strtotime";
        $x70 = "dirname";
        $x71 = "json_decode";
        $x72 = "count";
        return $this->cron_expr;
    }
    private function cleanString($x4d) {
        $x6d = "str_replace";
        $x6e = "trim";
        $x6f = "strtotime";
        $x70 = "dirname";
        $x71 = "json_decode";
        $x72 = "count";
        $x4d = $x6d("\	", "", $x4d);
        $x4d = $x6d("", "", $x4d);
        $x4d = $x6d("\
", "", $x4d);
        $x4d = $x6d("'", "", $x4d);
        return $x6e($x4d);
    }
    public function generateShipmentsFeed($x4e, $x4f = false) {
	
        $x6d = "str_replace";
        $x6e = "trim";
        $x6f = "strtotime";
        $x70 = "dirname";
        $x71 = "json_decode";
        $x72 = "count";
        $x4e = Mage::app()->getWebsite($x4e);
        $x50 = array();
        foreach ($x4e->getStoreIds() as $x51) $x50[] = $x51;
        $x52 = $x4e->getConfig("googletrustedstores/shipments_settings/previous_days");
        $x53 = $x6f(date("Y-m-d") . " " . $x52 . " day" . (($x52 == - 1) ? "" : "s"));
        $x53 = date("Y-m-d 00:00:00", $x53);
        $x54 = $x6f(date("Y-m-d") . " + 1 day");
        $x54 = date("Y-m-d 00:00:00", $x54);
        $x55 = array("ac" => "activation_code", "ak" => "activation_key", "bu" => "base_url", "md" => "md5", "th" => "this", "dm" => "_demo", "ext" => "gts", "ver" => "1.5.0");
        $x56 = "";
        if (!$x4f) {
		
            $x57 = new Varien_Io_File();
            $x58 = $x4e->getConfig("googletrustedstores/shipments_settings/filename");
            $x58 = $x4e->getCode() . "_" . $x58;
            $x59 = $x4e->getConfig("googletrustedstores/shipments_settings/filepath");
            $x5a = $x57->getCleanPath(Mage::getBaseDir() . "/" . $x59 . "/" . $x58);
            $x59 = $x70($x5a);
            if (!$x57->allowedPath($x59, Mage::getBaseDir())) {
                Mage::throwException(Mage::helper("googletrustedstores")->__("Please define correct path for the shipments feed ! (current : " . $x59 . ")"));
            }
            if (!$x57->fileExists($x59, false)) {
                Mage::throwException(Mage::helper("googletrustedstores")->__("Please create the specified folder '%s'.", Mage::helper("core")->htmlEscape($x59)));
            }
            if (!$x57->isWriteable($x59)) {
                Mage::throwException(Mage::helper("googletrustedstores")->__("Please make sure that '%s' is writable by web-server.", $x59));
            }
        }
        $x5b = Mage::app()->getStore()->getStoreId();
        $x5c = array("activation_key" => Mage::getStoreConfig("googletrustedstores/license/activation_key"), "activation_code" => Mage::getStoreConfig("googletrustedstores/license/activation_code"), "base_url" => Mage::getStoreConfig("web/secure/base_url"),);
		$amazon_email = "%@marketplace.amazon.com";
		
		//$x54 = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
		$x5d = Mage::getModel("sales/order")->getCollection()
			->addAttributeToFilter("created_at", array("from" => $x53, "to" => $x54))
			->addFieldToFilter("store_id", array("in" => $x50))
			//->addFieldToFilter("customer_email", array("nlike" => $amazon_email))
			->addAttributeToSort("created_at", "DESC");
        if ($x5c[$x55['ac']] != $x55["md"]($x55["md"]($x5c[$x55['ak']]) . $x55["md"]($x5c[$x55['bu']]) . $x55["md"]($x55["ext"]) . $x55["md"]($x55["ver"]))) {
            $$x55["ext"] = "valid";
            $$x55["th"]->$x55["dm"] = true;
        } else {
            $$x55["th"]->$x55["dm"] = false;
            $$x55["ext"] = "valid";
        }
        if (!$x4f) {
            $x57->setAllowCreateFolders(true);
            $x57->open(array("path" => $x59));
            $x57->streamOpen($x5a);
            $x57->streamWrite($this->HEADERS_s);
        } else {
            $x56.= $this->HEADERS_s;
        }
        if (!isset($$x55["ext"]) || $$x55["th"]->$x55["dm"]) {
            $$x55["th"]->$x55["dm"] = true;
            return $$x55["th"];
        }
        $x5e = $x71(Mage::getStoreConfig('googletrustedstores/carriers/carriers_mapping'));
        $x5f = array();
        foreach ($x5e as $x60) {
            $x5f[$x60->code] = $x60->as;
        }
		
        $x61 = Mage::helper('googletrustedstores/data');
        foreach ($x5d as $x62) {
            $x51 = $x62->getId();
            $x63 = Mage::getModel("sales/order")->load($x51);
			$order_data = $x63->getData();
			//echo  $x63->getIncrementId()."=====".$order_data['status']."<br />";
			$email = $order_data['customer_email'];
			$pick_date = $order_data['created_at'];
			$flag =0;
			$check_shipment_order = 0;
			if (strpos($email, "@amazon.com")!==false || strpos($email, "@marketplace.amazon.com")!==false) {
				$flag =1;
			}

            $x51 = $x63->getIncrementId();
            $x64 = $x63->getShipmentsCollection();
			
            foreach ($x64 as $x65) {
				$check_shipment_order =1;
                $x66 = $x65->getAllTracks();
                $x67 = $this->cleanString($x65->getUpdatedAt());
                if ($x67 != "") {
                    $x67 = $x6f($x67);
                    $x67 = date("Y-m-d", $x67);
                }
                if ($x72($x66)) {
                    foreach ($x66 as $x68) {
                        $x67 = $this->cleanString($x68->getUpdatedAt());
                        if ($x67 != "") {
                            $x67 = $x6f($x67);
							if($flag ==1) {
								$x67 = Mage::getModel('core/date')->date('Y-m-d', strtotime($pick_date));
							}else{
								$x67 = date("Y-m-d", $x67);
							}
                        }
						
                        $x69 = $this->cleanString($x68->getNumber());
                        //if ($x69 == "") continue;
						
						if($flag ==1) {
							$x69 = '';
						}
						
                        $x6a = "";
                        $x6b = $this->cleanString($x68->getCarrierCode());
                        $x6c = $x5f[$x6b];
                        if ($x6c != "") {
                            if ($x61->isOther($x6c)) {
                                $x6a = $x6c;
                                $x6c = "Other";
                            } else if ($x61->isCarrier($x6c)) {
                                $x6a = "";
                            } else {
                                $x6c = "Other";
                                $x6a = "OTHER";
                            }
                        } else {
							if($x68->getCarrierCode()=="usps"){
								if($flag == 1) {
									$x6c = "Other";
									$x6a = "";
								}else{
									$x6c = "USPS";
									$x6a = "";
								}	
							}else{
								$x6c = "Other";
								$x6a = "OTHER";
							}
                        }
                        if (!$x4f) {
                            $x57->streamWrite($x51 . $this->SEP . $x69 . $this->SEP . $x6c . $this->SEP . $x6a . $this->SEP . $x67 . $this->NEWLINE);
                        } else {
                            $x56.= $x51 . $this->SEP . $x69 . $this->SEP . $x6c . $this->SEP . $x6a . $this->SEP . $x67 . $this->NEWLINE;
                        }
                    }
                } else {
                    //continue;
                    if (!$x4f) {
                        $x57->streamWrite($x51 . $this->SEP . $this->SEP . "Other" . $this->SEP . "OTHER" . $this->SEP . $x67 . $this->NEWLINE);
                    } else {
                        $x56.= $x51 . $this->SEP . $this->SEP . "Other" . $this->SEP . "OTHER" . $this->SEP . $x67 . $this->NEWLINE;
                    }
                }
            }
			/*
			if($check_shipment_order==0){
			//if($check_shipment_order==0 && $order_data['status']=="processing"){
					if (!$x4f) {
                        $x57->streamWrite($x51 . $this->SEP . $this->SEP . $this->SEP . $this->SEP . $this->NEWLINE);
                    } else {
                        $x56.= $x51 . $this->SEP . $this->SEP . $this->SEP . $this->SEP . $this->NEWLINE;
                    }
			}*/
			
        }

        if ($x4f) {
            return $x56;
        }
        $x57->streamClose();
        return true;
    }
    public function generateCancellationsFeed($x4e, $x4f = false) {
        $x6d = "str_replace";
        $x6e = "trim";
        $x6f = "strtotime";
        $x70 = "dirname";
        $x71 = "json_decode";
        $x72 = "count";
        $x4e = Mage::app()->getWebsite($x4e);
        $x50 = array();
        foreach ($x4e->getStoreIds() as $x51) $x50[] = $x51;
        $x56 = "";
        $x52 = $x4e->getConfig("googletrustedstores/cancellations_settings/previous_days");
        $x53 = $x6f(date("Y-m-d") . " " . $x6d('-', '- ', $x52) . " day" . (($x52 == - 1) ? "" : "s"));
        $x53 = date("Y-m-d 00:00:00", $x53);
        $x54 = $x6f(date("Y-m-d") . " + 1 day");
        $x54 = date("Y-m-d 00:00:00", $x54);
        if (!$x4f) {
            $x57 = new Varien_Io_File();
            $x58 = $x4e->getConfig("googletrustedstores/cancellations_settings/filename");
            $x58 = $x4e->getCode() . "_" . $x58;
            $x59 = $x4e->getConfig("googletrustedstores/cancellations_settings/filepath");
            $x5a = $x57->getCleanPath(Mage::getBaseDir() . "/" . $x59 . "/" . $x58);
            $x59 = $x70($x5a);
            if (!$x57->allowedPath($x59, Mage::getBaseDir())) {
                Mage::throwException(Mage::helper("googletrustedstores")->__("Please define correct path for the cancellations feed ! (current : " . $x59 . ")"));
            }
            if (!$x57->fileExists($x59, false)) {
                Mage::throwException(Mage::helper("googletrustedstores")->__("Please create the specified folder '%s'.", Mage::helper("core")->htmlEscape($x59)));
            }
            if (!$x57->isWriteable($x59)) {
                Mage::throwException(Mage::helper("googletrustedstores")->__("Please make sure that '%s' is writable by web-server.", $x59));
            }
        }
		$amazon_email = "%@marketplace.amazon.com";
        $x5d = Mage::getModel("sales/order")->getCollection()
			->addFieldToFilter("created_at", array("from" => $x53, "to" => $x54))
			->addFieldToFilter("store_id", array("in" => $x50))
			//->addFieldToFilter("customer_email", array("nlike" => $amazon_email))
			->addAttributeToFilter("status", "canceled")
			->addAttributeToSort("created_at", "DESC");
        if (!$x4f) {
            $x57->setAllowCreateFolders(true);
            $x57->open(array("path" => $x59));
            $x57->streamOpen($x5a);
            $x57->streamWrite($this->HEADERS_c);
        } else {
            $x56.= $this->HEADERS_c;
        }
        foreach ($x5d as $x62) {
            $x51 = $x62->getIncrementId();
            if (!$x4f) {
                $x57->streamWrite($x51 . $this->SEP . "MerchantCanceled" . $this->NEWLINE);
            } else {
                $x56.= $x51 . $this->SEP . "MerchantCanceled" . $this->NEWLINE;
            }
        }
        if ($x4f) {
            return $x56;
        }
        $x57->streamClose();
        return true;
    }
};
?>