<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Orderpreparation_Helper_FieldFormat extends Mage_Core_Helper_Abstract {

    /**
     * Return an array with all format functions
     *
     */
    public function getFieldFormaters() {
        $retour = array();

        $retour['pad_left'] = 'Pad left';
        $retour['pad_right'] = 'Pad right';
        $retour['date_format'] = 'Date format';
        $retour['number_format'] = 'Number format';
        $retour['custom_value'] = 'Custom value';
        $retour['custom_list'] = 'Custom list';
        $retour['ceil'] = 'Ceil';
        $retour['gr_to_kg'] = 'Grams to Kilog';

        return $retour;
    }

    public function getFieldDelimiters() {
        $retour = array();

        $retour['none'] = 'none';
        $retour['quote'] = '\'';
        $retour['doublequote'] = '\'\'';

        return $retour;
    }

    public function getFieldSeparators() {
        $retour = array();

        $retour['none'] = 'none';
        $retour['coma'] = ',';
        $retour['pipe'] = '|';
        $retour['semicolon'] = ';';
        $retour['tab'] = '[tab]';


        return $retour;
    }

    public function getFileFormats() {
        $retour = array();

        $retour['fixed'] = 'fixed';
        $retour['delimiter'] = 'delimiter';

        return $retour;
    }

    public function getLineEnds() {
        $retour = array();

        $retour['r'] = '\r';
        $retour['n'] = '\n';
        $retour['rn'] = '\r\n';

        return $retour;
    }

    public function getImportContents() {
        $retour = array();

        $retour[''] = 'Not used';
        $retour['tracking'] = 'Tracking';
        $retour['shipment'] = 'Shipment';

        return $retour;
    }

}

