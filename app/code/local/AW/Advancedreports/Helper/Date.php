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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_Helper_Date extends AW_Advancedreports_Helper_Data
{
    /**
     * Retrieves formatted datetime
     * Implements standard strptime() for cross-platformed use
     *
     * @param Datetime $sDate
     * @param string   $sFormat
     *
     * @return string
     */
    public function strptime($sDate, $sFormat)
    {
        $aResult = array(
            'tm_sec'   => 0,
            'tm_min'   => 0,
            'tm_hour'  => 0,
            'tm_mday'  => 1,
            'tm_mon'   => 0,
            'tm_year'  => 0,
            'tm_wday'  => 0,
            'tm_yday'  => 0,
            'unparsed' => $sDate,
        );

        while ($sFormat != "") {
            // ===== Search a %x element, Check the static string before the %x =====
            $nIdxFound = strpos($sFormat, '%');
            if ($nIdxFound === false) {
                // There is no more format. Check the last static string.
                $aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
                break;
            }

            $sFormatBefore = substr($sFormat, 0, $nIdxFound);
            $sDateBefore = substr($sDate, 0, $nIdxFound);

            if ($sFormatBefore != $sDateBefore) {
                break;
            }

            // ===== Read the value of the %x found =====
            $sFormat = substr($sFormat, $nIdxFound);
            $sDate = substr($sDate, $nIdxFound);

            $aResult['unparsed'] = $sDate;

            $sFormatCurrent = substr($sFormat, 0, 2);
            $sFormatAfter = substr($sFormat, 2);

            $nValue = -1;
            $sDateAfter = "";

            switch ($sFormatCurrent) {
                case '%S': // Seconds after the minute (0-59)

                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if (($nValue < 0) || ($nValue > 59)) {
                        return false;
                    }

                    $aResult['tm_sec'] = $nValue;
                    break;

                // ----------
                case '%M': // Minutes after the hour (0-59)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if (($nValue < 0) || ($nValue > 59)) {
                        return false;
                    }

                    $aResult['tm_min'] = $nValue;
                    break;

                // ----------
                case '%H': // Hour since midnight (0-23)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if (($nValue < 0) || ($nValue > 23)) {
                        return false;
                    }

                    $aResult['tm_hour'] = $nValue;
                    break;

                // ----------
                case '%e':
                case '%d': // Day of the month (1-31)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                if (($nValue < 1) || ($nValue > 31)) {
                    return false;
                }

                    $aResult['tm_mday'] = $nValue;
                    break;

                // ----------
                case '%m': // Months since January (0-11)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if (($nValue < 1) || ($nValue > 12)) {
                        return false;
                    }

                    $aResult['tm_mon'] = ($nValue - 1);
                    break;

                // ----------
                case '%y': // Years since 1900
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if ($nValue >= 69 && $nValue <= 99) {
                        $aResult['tm_year'] = $nValue;
                    } else {
                        $aResult['tm_year'] = ($nValue + 100);
                    }
                    break;

                // ----------
                case '%Y': // Years since 1900

                    sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);

                    if ($nValue < 1900) {
                        return false;
                    }

                    $aResult['tm_year'] = ($nValue - 1900);
                    break;

                // ----------
                default:
                    break 2; // Break Switch and while

            } // END of case format

            // ===== Next please =====
            $sFormat = $sFormatAfter;
            $sDate = $sDateAfter;
            $aResult['unparsed'] = $sDate;
        } // END of while($sFormat != "")

        // ===== Create the other value of the result array =====
        $nParsedDateTimestamp = mktime(
            $aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'],
            $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900
        );

        // Before PHP 5.1 return -1 when error
        if (
            ($nParsedDateTimestamp === false)
            || ($nParsedDateTimestamp === -1)
        ) {
            return false;
        }

        $aResult['tm_wday'] = (int)strftime("%w", $nParsedDateTimestamp); // Days since Sunday (0-6)
        $aResult['tm_yday'] = (strftime("%j", $nParsedDateTimestamp) - 1); // Days since January 1 (0-365)

        return $aResult;
    }

    public function incSec($datetime)
    {
        $date = new Zend_Date(
            $datetime,
            self::MYSQL_ZEND_DATE_FORMAT,
            $this->getLocale()->getLocaleCode()
        );
        $date->addSecond(1);
        return $date->toString(self::MYSQL_ZEND_DATE_FORMAT);
    }

    public function decSec($datetime)
    {
        $date = new Zend_Date(
            $datetime,
            self::MYSQL_ZEND_DATE_FORMAT,
            $this->getLocale()->getLocaleCode()
        );
        $date->subSecond(1);
        return $date->toString(self::MYSQL_ZEND_DATE_FORMAT);
    }

    /**
     * Retrieves day period (timezone offset is included)
     *
     * @param string $datetime
     *
     * @return array
     */
    public function getThisDayPeriod($datetime)
    {
        $date = new Zend_Date(
            $datetime,
            Zend_Date::ISO_8601
        );

        $dateFrom = clone $date;
        $dateFrom->setHour(0)->setMinute(0)->setSecond(0)->addSecond($this->getTimeZoneOffset());

        $dateTo = clone $date;
        $dateTo->setHour(23)->setMinute(59)->setSecond(59)->addSecond($this->getTimeZoneOffset());

        return array(
            'from' => $dateFrom->toString(self::MYSQL_ZEND_DATE_FORMAT),
            'to'   => $dateTo->toString(self::MYSQL_ZEND_DATE_FORMAT)
        );
    }

    public function toTimestamp($date, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
    {
        $dateFormat = Mage::app()->getLocale()->getDateFormat($format);
        $dateObj = new Zend_Date();
        if ($date) {
            try {
                $dateObj->setDate($date, $dateFormat);
            } catch (Exception $ex) {
                try {
                    $dateObj->setDate($date);
                } catch (Exception $ex) {
                    if ($timestamp = @strtotime($date)) {
                        $dateObj->setTimestamp($timestamp);
                    }
                }
            }
            $dateObj->setTime(0);
        }
        return $dateObj->getTimestamp();
    }

    public function fromTimestamp($timestamp, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
    {
        $dateFormat = Mage::app()->getLocale()->getDateFormat($format);
        $date = new Zend_Date();
        $date->setTimestamp($timestamp);
        return $date->toString($dateFormat);
    }
}
