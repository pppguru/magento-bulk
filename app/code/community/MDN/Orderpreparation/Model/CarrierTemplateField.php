<?php

class MDN_Orderpreparation_Model_CarrierTemplateField extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('Orderpreparation/CarrierTemplateField');
    }

    /**
     * Return field value for the current order
     *
     * @param unknown_type $data
     * @return unknown
     */
    public function getValue($data) {
        $value = null;

        //retrieve value
        if ($this->isCustomField()) {
            if (isset($data[$this->getCode()]))
                $value = $data[$this->getCode()];
            else
                $value = $this->getctf_content();
        }
        else {
            $value = $this->getctf_content();
            $regExp = '*({[^}]+})*';
            preg_match_all($regExp, $this->getctf_content(), $result, PREG_OFFSET_CAPTURE);
            foreach ($result[0] as $item) {
                $code = str_replace('{', '', str_replace('}', '', $item[0]));
                if (isset($data[$code]))
                    $value = str_replace($item[0], $data[$code], $value);
                else
                    $value = str_replace($item[0], '', $value);
            }
        }
        $value = trim($value);

        //replace accent (if required)
        if ($this->getParentTemplate() != null) {
            if ($this->getParentTemplate()->getct_export_remove_accent() == 1)
                $value = $this->removeAccent($value);
        }

        //format value (if needed)
        $format = $this->getctf_format();
        $size = $this->getctf_size();
        $argument = $this->getctf_format_argument();
        if ($format != '')
            $value = $this->applyFormat($format, $argument, $value, $size);


        //format for fixed size (if formater didn't)
        if ($this->getParentTemplate() != null) {
            if ($this->getParentTemplate()->getct_export_format() == 'fixed') {
                $value = $this->TruncateIfTooLarge($value, $size);
                if (strlen($value) != $size)
                    $value = str_pad($value, $size, ' ');
            }
        }

        return $value;
    }

    /**
     * Enter description here...
     *
     */
    public function isCustomField() {
        $retour = false;
        switch ($this->getctf_format()) {
            case 'custom_value':
            case 'custom_list':
                $retour = true;
                break;
        }
        return $retour;
    }

    /**
     * 
     *
     * @param unknown_type $orderToPrepare
     */
    public function getCustomFieldControl($data, $namePrefix = 'custom_values') {
        $retour = '';
        if ($this->isCustomField()) {
            $value = $this->getValue($data);
            $name = $namePrefix . '[' . $this->getCode() . ']';
            switch ($this->getctf_format()) {
                case 'custom_value':
                    $retour = '<input type="text" name="' . $name . '" id="' . $name . '" value="' . $value . '">';
                    break;
                case 'custom_list':
                    $retour = '<select name="' . $name . '" id="' . $name . '">';
                    $t = explode(';', $this->getctf_format_argument());
                    foreach ($t as $row) {
                        $t_row = explode(':', $row);
                        if (count($t_row) == 2) {
                            $selected = '';
                            if ($value == $t_row[0])
                                $selected = ' selected ';
                            $retour .= '<option value="' . $t_row[0] . '" ' . $selected . '>' . $t_row[1] . '</option>';
                        }
                    }
                    $retour .= '</select>';
                    break;
            }
        }
        return $retour;
    }

    /**
     * return field code from name
     *
     * @return unknown
     */
    public function getCode() {
        $retour = strtolower($this->getctf_name());
        $retour = str_replace(' ', '_', $retour);
        return $retour;
    }

    /**
     * Truncate value if too large
     *
     * @param unknown_type $string
     * @param unknown_type $maxlength
     * @return unknown
     */
    private function TruncateIfTooLarge($string, $maxlength) {
        if (strlen($string) > $maxlength)
            $string = substr($string, 0, $maxlength);

        return $string;
    }

    /**
     * Apply format
     *
     * @param unknown_type $format
     * @param unknown_type $argument
     * @param unknown_type $value
     */
    private function applyFormat($format, $argument, $value, $size) {
        switch ($format) {
            case 'date_format':
                $value = strtotime($value);
                $value = date($argument, $value);
                break;
            case 'number_format':
                $value = sprintf($argument, $value);
                break;
            case 'gr_to_kg':
                $value = (float)($value / 1000);
                break;
            case 'pad_left':
                $value = sprintf('%' . $argument . $size . 's', $value);
                break;
            case 'pad_right':
                $value = sprintf('%-' . $argument . $size . 's', $value);
                break;
            case 'ceil':
                if (is_numeric($value))
                    $value = ceil($value);
                break;
        }

        return $value;
    }

    /**
     * Remove accents
     *
     * @param unknown_type $string
     */
    private function removeAccent($string) {
        $string = utf8_decode($string);
        $string = strtr($string,  "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",  "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
        return utf8_encode($string);
    }

}