<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Product Feeds
 * @version   1.1.2
 * @build     428
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_FeedExport_Helper_Eval extends Mage_Core_Helper_Abstract
{
    public function execute($value, $formatterLine)
    {
        $formatter = explode(' ', $formatterLine);
        $method = $formatter[0];

        array_shift($formatter);
        $args = $formatter;
        if (!is_array($args)) {
            $args = array();
        }

        if (function_exists($method)) {
            foreach ($args as $key => $arg) {
                if (!is_numeric($arg)) {
                    $args[$key] = "'".$arg."'";
                }
            }

            $cmd = 'return '.$method.'("'.addslashes($value).'"';

            if (count($args)) {
                $cmd .= ','.implode(',', $args).'';
            }

            $cmd .= ');';

            $value = @eval($cmd);
        } elseif (method_exists($this, $method)) {
            array_unshift($args, $value);
            $value = call_user_func_array(array($this, $method), $args);
        } else {
            $value .= $formatterLine;
        }

        return $value;
    }

    public function convert($value, $currency)
    {
        $value = Mage::helper('directory')->currencyConvert($value, Mage::app()->getStore()->getBaseCurrencyCode(), $currency);
        $value = number_format($value, 2, '.', '');

        return $value;
    }

    public function csvPretty($value, $delimiter)
    {
        if ($delimiter == 'tab') {
            $delimiter = "\t";
        }

        $value = str_replace("\n", ' ', $value);
        $value = str_replace("\r", '', $value);
        $value = str_replace("\t", '', $value);
        $value = str_replace($delimiter, ' ', $value);

        return $value;
    }

    public function html2plain($value)
    {
        // 194 -> 32
        $value = str_replace('Â ', ' ', $value);

        $value = strip_tags($value);

        $value = str_replace('\\\'', '\'', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    public function urlToUnsecure($value)
    {
        return str_replace('https', 'http', $value);
    }

    public function urlToSecure($value)
    {
        return str_replace('http', 'https', $value);
    }
}