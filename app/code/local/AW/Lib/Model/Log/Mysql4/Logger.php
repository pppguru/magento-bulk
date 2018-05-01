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


class AW_Lib_Model_Log_Mysql4_Logger extends Mage_Core_Model_Mysql4_Abstract
{

    const BACKUP_DEFAULT_FILE_NAME = "_aw_log.txt";
    const BACKUP_DEFAULT_ARCHIVE_NAME = "_aw_log.zip";
    const PATH_TO_BACKUP_DIR = '/var/aw_logs_backup/';

    protected function _construct()
    {
        $this->_init('aw_lib/logger', 'id');
    } 
    
    /**
    * Return table part data SQL insert
    *
    * @param string $tableName
    * @param string $select
    * @return string
    */
    public function getPartInsertSql($tableName, $select)
    {
        $sql = null;
        $adapter = $this->_getWriteAdapter();
        $query  = $adapter->query($select);

        while ($row = $query->fetch()) {
            if ($sql === null) {
                $sql = sprintf('INSERT INTO %s VALUES ', $adapter->quoteIdentifier($tableName));
            } else {
                $sql .= ',';
            }

            $sql .= $this->_quoteRow($tableName, $row);
        }

        if ($sql !== null) {
            $sql .= ';' . "\n";
        }

        return $sql;
    }
    
    /**
    * Set the backup file content
    *
    * @param string $content
    * @return AW_Lib_Model_Log_Logger
    */
    public function createBackupFile($content)
    {
        if (!extension_loaded("zlib") || !$content) {
            return $this;
        }
        
        $date = new Zend_Date();
        $fileName = $date->toString(Varien_Date::DATE_INTERNAL_FORMAT) . self::BACKUP_DEFAULT_FILE_NAME;
        $pathToBackupDir = Mage::getBaseDir() . self::PATH_TO_BACKUP_DIR;
        $pathToBackupFile = $pathToBackupDir . $fileName;

        $backupFile = fopen($pathToBackupFile, 'w');
        if (!$backupFile) {
            return $this;
        }
        $fwrite = fwrite($backupFile, $content);
        if (!$fwrite) {
            fclose($backupFile);
            unlink($pathToBackupFile);
            return $this;
        }
        $archiveName = $date->toString(Varien_Date::DATE_INTERNAL_FORMAT) . self::BACKUP_DEFAULT_ARCHIVE_NAME;
        $pathToBackupArchive = $pathToBackupDir . $archiveName;

        $zipArchive = new ZipArchive();
        $zipArchive->open($pathToBackupArchive, ZIPARCHIVE::CREATE);
        $zipArchive->addFile($pathToBackupFile, $fileName);
        $zipArchive->close();

        fclose($backupFile);
        unlink($pathToBackupFile);
        
        return $this;
    }
    
    /**
    * Quote Table Row
    *
    * @param string $tableName
    * @param array $row
    * @return string
    */
    protected function _quoteRow($tableName, array $row)
    {
        $adapter   = $this->_getReadAdapter();
        $describe  = $adapter->describeTable($tableName);
        $dataTypes = array('bigint', 'mediumint', 'smallint', 'tinyint');
        $rowData   = array();
        foreach ($row as $k => $v) {
            if ($v === null) {
                $value = 'NULL';
            } elseif (in_array(strtolower($describe[$k]['DATA_TYPE']), $dataTypes)) {
                $value = $v;
            } else {
                $value = $adapter->quoteInto('?', $v);
            }
            $rowData[] = $value;
        }

        return sprintf('(%s)', implode(',', $rowData));
    }

}