<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Helper_Export extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv($data = array())
    {
        $csv = '';

        $headers = array();
        if (!array_key_exists('headers',$data)) {
            $data['headers'] = array();
        }
        foreach ($data['headers'] as $header) {
            $headers[] = '"' . $header . '"';
        }
        $csv.= implode(',', $headers)."\n";

        if (!array_key_exists('items',$data)) {
            $data['items'] = array();
        }
        foreach ($data['items'] as $item) {
            $row = array();
            foreach ($item as $column) {
                if (is_array($column)) {
                    $column = implode(',', $column);
                }
                $row[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $column) . '"';
            }
            $csv.= implode(',', $row)."\n";
        }
        $total = array();
        if (!array_key_exists('totals',$data)) {
            $data['totals'] = array();
        }

        foreach ($data['totals'] as $column) {
            $total[] = '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $column) . '"';
        }
        $csv.= implode(',', $total)."\n";

        return $csv;
    }

    public function getExcel($data = array(), $filename = '')
    {
        $excelData = array();
        if (array_key_exists('headers',$data)) {
            $excelData[] = $data['headers'];
        }

        if (array_key_exists('items',$data)) {
            foreach ($data['items'] as $item) {
                $excelData[] = $item;
            }
        }

        if (array_key_exists('totals',$data)) {
            $excelData[] = $data['totals'];
        }

        $xmlObj = new Varien_Convert_Parser_Xml_Excel();
        $xmlObj->setVar('single_sheet', $filename);
        $xmlObj->setData($excelData);
        $xmlObj->unparse();

        return $xmlObj->getData();
    }
}