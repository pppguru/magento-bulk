<?php

class Wyomind_Googletrustedstores_Block_Adminhtml_System_Config_Form_Field_Carriers extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $mapping = Mage::getStoreConfig('googletrustedstores/carriers/carriers_mapping');
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $options = array();

        foreach ($methods as $_ccode => $_carrier) {
            
            $_methodOptions = array();
            if ($_carrier->getAllowedMethods()) {

                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;

                $options[] = array('value' => $_methodOptions, 'label' => $_title, 'code' => $_ccode);
            }
        }

        $carrier_code = Mage::helper('googletrustedstores/data')->carrier_code;
        $other_carrier_code = Mage::helper('googletrustedstores/data')->other_carrier_code;
        
        foreach ($carrier_code as $label => $code) {
            $gts_carrier_codes[] = "<option value='" . $code . "'>" . $label . "</option>";
        }
        foreach ($other_carrier_code as $label => $code) {
            $gts_other_carrier_codes[] = "<option value='" . $code . "'>" . $label . "</option>";
        }

        $html = "<script>
                document.observe('dom:loaded', function() {

                    $$('.gts_carriers_mapping').each(function(e) {
                        e.observe('change', function() {
                            mapping = new Array;
                            $$('.gts_carriers_mapping').each(function(d) {
                                mapping.push({code:d.id, as:d.value});
                            });
                            googletrustedstores_carriers_carriers_mapping.value=Object.toJSON(mapping);
                        });
                    });
                    
                    mapping=" . $mapping . ";
                    mapping.each(function(carrier){
                        if ($(carrier.code))
                            $(carrier.code).value=carrier.as;
                   });
                });
            </script>";




        
        foreach ($options as $option) {
            $html.= "<div style='border: 0px solid grey; margin: 15px; padding: 5px; width: 550px;'><b>" . $option['label'] . " </b> [code : " . $option["code"] . "] ";
            $html.="<select class='gts_carriers_mapping' id='" . $option["code"] . "' name='" . $option["code"] . "' style='float:right;'><option value='OTHER'>All others carriers</option><optgroup label='Carriers'>" . implode("\n\r", $gts_carrier_codes) . "</optgroup><optgroup label='Other carriers'>" . implode("\n\r", $gts_other_carrier_codes) . "</optgroup></select>";
            $html.="</div>";
        };

        $html.='<input style="width:1000px" type="hidden" value=\'' . $mapping . '\' name="groups[carriers][fields][carriers_mapping][value]" id="googletrustedstores_carriers_carriers_mapping">';
        return $html;
    }

}

?>
