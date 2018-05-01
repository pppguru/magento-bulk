<?php

class MDN_SmartReport_Model_Report extends Mage_Core_Model_Abstract
{

    const kTypeCustomer = 'customer';
    const kTypeProduct = 'product';
    const kTypeSales = 'sales';

    protected static $_allReports = null;

    protected $_cache = array();

    protected static function getAllReports()
    {
        if (self::$_allReports == null)
        {
            $files = array();
            $files[] = Mage::getBaseDir('lib').DS.'mdn'.DS.'smartreports'.DS.'reports.xml';
            if (Mage::helper('SmartReport')->erpIsInstalled())
                $files[] = Mage::getBaseDir('lib').DS.'mdn'.DS.'smartreports'.DS.'erp.xml';
            if (file_exists(Mage::getBaseDir('lib').DS.'mdn'.DS.'smartreports'.DS.'pos.xml'))
                $files[] = Mage::getBaseDir('lib').DS.'mdn'.DS.'smartreports'.DS.'pos.xml';
            $files[] = Mage::getBaseDir('lib').DS.'mdn'.DS.'smartreports'.DS.'custom.xml';

            self::$_allReports = array();

            foreach($files as $filePath) {
                if (!file_exists($filePath))
                    continue;

                $elts = simplexml_load_file($filePath);


                foreach ((array)$elts as $k => $elt) {
                    $obj = Mage::getModel('SmartReport/Report');

                    $reportData = (array)$elt;
                    $obj->setData($reportData);
                    $obj->setKey($k);


                    self::$_allReports[$k] = $obj;
                }
            }

            //populate inherits
            foreach(self::$_allReports as $report)
            {
                if ($report->getInherits())
                {
                    if (!isset(self::$_allReports[$report->getInherits()]))
                        throw new Exception('Unable to inherit from '.$report->getInherits());

                    $parent = self::$_allReports[$report->getInherits()];
                    foreach($parent->getData() as $k => $v)
                    {
                        if ($report->getData($k) == '') {
                            //Mage::helper('SmartReport')->log($report->getKey().' inherits of '.$k.' from '.$parent->getKey().' : '.$report->getData($k).' > '.$parent->getData($k));
                            $report->setData($k, $v);
                        }
                    }
                }
            }

        }

        return self::$_allReports;

    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('SmartReport/Report');
    }

    public function getReports($group = null)
    {
        $reports = array();

        foreach(self::getAllReports() as $obj)
        {
            if ($obj->getData('disable') == 1)
                continue;

            $groups = explode(',', $obj->getData('group'));
            if ($group && (!in_array($group, $groups)))
                continue;

            $reports[] = $obj;
        }

        return $reports;
    }

    public function getReportById($id)
    {
        foreach(self::getAllReports() as $report)
        {
            if ($report->getId() == $id)
                return $report;
        }
    }

    public function getReportByKey($key)
    {
        foreach(self::getAllReports() as $report)
        {
            if ($report->getKey() == $key)
                return $report;
        }
    }

    public function render()
    {
        try
        {
            $block = null;
            switch($this->getRenderer())
            {
                case 'line':
                    $block = Mage::app()->getLayout()->createBlock('SmartReport/Report_Renderer_Line');
                    break;
                case 'bar':
                    $block = Mage::app()->getLayout()->createBlock('SmartReport/Report_Renderer_Bar');
                    break;
                case 'multibar':
                    $block = Mage::app()->getLayout()->createBlock('SmartReport/Report_Renderer_MultiBar');
                    break;
                case 'stackbar':
                    $block = Mage::app()->getLayout()->createBlock('SmartReport/Report_Renderer_StackBar');
                    break;
                case 'area':
                    $block = Mage::app()->getLayout()->createBlock('SmartReport/Report_Renderer_Area');
                    break;
                case 'pie':
                    $block = Mage::app()->getLayout()->createBlock('SmartReport/Report_Renderer_Pie');
                    break;
                case 'table':
                    $block = Mage::app()->getLayout()->createBlock('SmartReport/Report_Renderer_Table');
                    break;
                default:
                    throw new Exception('Renderer not supported : '.$this->getRenderer());
            }
            $block->setReport($this);

            $html = $block->toHtml();
        }
        catch(Exception $ex)
        {

            Mage::helper('SmartReport')->log($ex->getMessage());

            $html = 'An error occured for report : '.$this->getName().'<br>'.$ex->getMessage();
            $html .= '<p>Report details :<pre>';
            var_dump($this->getData());
            echo '</pre>';
            $html .= '<p>&nbsp;</p><pre>';$ex->getTraceAsString().'</pre>';
        }

        return $html;
    }

    public function getReportDatas($limit)
    {

        if ($this->getModel())
        {
            $model = Mage::getModel($this->getModel());
            $cacheKey = $model->getCacheKey($this->getVariables(),$limit);
            if (!isset($this->_cache[$cacheKey]))
            {
                $this->_cache[$cacheKey] = $model->getData($this->getVariables(),$limit);
                Mage::helper('SmartReport')->log('getReportDatas for '.$this->getName().' from model');
            }
            else
            {
                Mage::helper('SmartReport')->log('getReportDatas for '.$this->getName().' from model CACHED');
            }
            return $this->_cache[$cacheKey];
        }
        else
        {
            $sql = $this->getsql();

            if ($limit)
                $sql .= " limit 0,".$limit;
            $sql = Mage::helper('SmartReport')->replaceCodes($sql, $this->getVariables());

            if (!isset($this->_cache[$sql]))
            {

                Mage::helper('SmartReport')->log('Query for report data #'.$this->getKey());
                $this->_cache[$sql] = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
                Mage::helper('SmartReport')->log('getReportDatas for '.$this->getName().' from query');
            }
            else
            {
                Mage::helper('SmartReport')->log('getReportDatas for '.$this->getName().' from query CACHED');
            }

            return $this->_cache[$sql];
        }

    }

    public function getReportDetails()
    {
        $sql = $this->getExtractSql();

        $sql = Mage::helper('SmartReport')->replaceCodes($sql, $this->getVariables());


        return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    }


    public static function sortByWidth($a, $b)
    {
        $a = (int)$a->getWidth();
        $b = (int)$b->getWidth();

        if ($a < $b)
            return 1;
        else
            return -1;
    }
}