<?php

class Wyomind_Googletrustedstores_Model_System_Config_Source_Days {

    public function getValues() {
        $ret = array(
            array('label'=>"1 day",'value'=>"-1"),
            array('label'=>"2 days",'value'=>"-2"),
            array('label'=>"3 days",'value'=>"-3"),
            array('label'=>"4 days",'value'=>"-4"),
            array('label'=>"5 days",'value'=>"-5"),
            array('label'=>"6 days",'value'=>"-6"),
            array('label'=>"7 days",'value'=>"-7"),
            array('label'=>"8 days",'value'=>"-8"),
            array('label'=>"9 days",'value'=>"-9"),
            array('label'=>"10 days",'value'=>"-10"),
            array('label'=>"15 days",'value'=>"-15"),
            array('label'=>"20 days",'value'=>"-20"),
            array('label'=>"30 days",'value'=>"-30"),
            array('label'=>"45 days",'value'=>"-45"),
            array('label'=>"60 days",'value'=>"-60"),
        );
        return $ret;
    }
    
    
    public function toOptionArray() {
        return $this->getValues();
    }
    public function toArray() {
        return $this->getValues();
    }

}