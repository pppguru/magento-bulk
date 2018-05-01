<?php

class MDN_Shipworks_Helper_Xml extends Mage_Core_Helper_Abstract {

    /**
     * 
     */
    public function writeXmlDeclaration() {
        echo "<?xml version=\"1.0\" standalone=\"yes\" ?>";
    }

    /**
     * 
     * @param type $tag
     * @param type $attributes
     */
    public function writeStartTag($tag, $attributes = null) {
        echo '<' . $tag;

        if ($attributes != null) {
            echo ' ';

            foreach ($attributes as $name => $attribValue) {
                echo $name . '="' . htmlspecialchars($attribValue) . '" ';
            }
        }

        echo '>';
    }

    /**
     * write closing xml tag
     * @param type $tag
     */
    public function writeCloseTag($tag) {
        echo '</' . $tag . '>';
    }

    /**
     * Output the given tag\value pair
     * @param type $tag
     * @param type $value
     */
    public function writeElement($tag, $value) {
        $this->writeStartTag($tag);
        echo htmlspecialchars($value);
        $this->writeCloseTag($tag);
    }

    /**
     * Outputs the given name/value pair as an xml tag with attributes
     * @param type $tag
     * @param type $value
     * @param type $attributes
     */
    public function writeFullElement($tag, $value, $attributes) {
        echo '<' . $tag . ' ';

        foreach ($attributes as $name => $attribValue) {
            echo $name . '="' . htmlspecialchars($attribValue) . '" ';
        }
        echo '>';
        echo htmlspecialchars($value);
        $this->writeCloseTag($tag);
    }
    
    /**
     * Function used to output an error and quit.
     * @param type $code
     * @param type $error
     */
    function outputError($code, $error)
    {       
            $this->writeStartTag("Error");
            $this->writeElement("Code", $code);
            $this->writeElement("Description", $error);
            $this->writeCloseTag("Error");
    }

}
