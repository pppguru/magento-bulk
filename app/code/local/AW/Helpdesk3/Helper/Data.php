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


class AW_Helpdesk3_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
    * Recursively searches and replaces all occurrences of search in subject values
    * replaced with the given replace value
    * @param string $search The value being searched for
    * @param string $replace The replacement value
    * @param array $subject Subject for being searched and replaced on
    * @return array Array with processed values
    */
    public function recursiveReplace($search, $replace, $subject)
    {
        if (!is_array($subject)) {
            return $subject;
        }
        foreach ($subject as $key => $value) {
            if (is_string($value)) {
                $subject[$key] = str_replace($search, $replace, $value);
            } elseif (is_array($value)) {
                $subject[$key] = self::recursiveReplace($search, $replace, $value);
            }
        }
        return $subject;
    }

    /**
     * In Byte
     * @param      int $fileSize
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public function isAllowFileSize($fileSize, $store = null)
    {
        if (($fileSize / 1024 / 1024) <= AW_Helpdesk3_Helper_Config::getMaxUploadFileSizeOnFrontend($store)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $filename
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     *
     * @return bool
     */
    public function isAllowFileExtension($filename, $store = null)
    {
        $allowedFileExtension = AW_Helpdesk3_Helper_Config::getAllowFileExtension($store);
        if (!$allowedFileExtension) {
            return true;
        }
        $allowedFileExtension = explode(',', $allowedFileExtension);

        $allowedFileExtension = array_map('strtolower', $allowedFileExtension);
        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($fileExt), $allowedFileExtension)) {
            return true;
        }
        return false;
    }

    public function escapeFilename($fileName)
    {
        return preg_replace('/([#=\/*:\?<>\| \\\"\'])+/i', '', $fileName);
    }

    public function getCustomerCollectionByEmail($email, $limit)
    {
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->setPageSize($limit)
        ;
        $collection->addAttributeToFilter(
            array(
                array('attribute' => 'email', 'like' => '%' . $email . '%')
            )
        );
        return $collection;
    }

    public function validateAttach($filename, $content, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        if (!$this->isAllowFileSize(strlen($content), $storeId)) {
            throw new Exception('Attachment can\'t be saved because is too large');
        }
        if (!$this->isAllowFileExtension($filename, $storeId)) {
            throw new Exception('Attachment can\'t be saved because is not allowed extension');
        }
        return true;
    }
}