<?php

/**
 * Class MyExtensions
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

class MDN_MyBms_Block_MyExtensions extends Mage_Adminhtml_Block_Abstract
{
    protected $_template = 'MyBms/System/Config/Form/MyExtensions.phtml';

    /**
     * @return array
     */
    public function listMyExtensions()
    {
       return  Mage::helper('MyBms/MyExtensions')->listMyExtensions();
    }

    /**
     * link to start download changelog
     *
     * @param $name of module
     * @return string
     */
    public function getDownloadChangelogUrl($name,$release) {
        return $this->getUrl('adminhtml/MyBms_ChangeLog/DownloadChangeLog', array('name' => $name, 'release' => $release));
    }

}