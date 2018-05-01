<?php

/**
 * Class ChangeLogController
 *
 * @package MyBms
 * @author Alan Le Goux <contact@boostmyshop.com>
 * @copyright 2016 BoostMyShop (http://www.boostmyshop.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MDN_MyBms_Adminhtml_MyBms_ChangeLogController extends Mage_Adminhtml_Controller_Action
{
    /**
     * prepare the download of the changelog in a .txt
     */
    public function DownloadChangeLogAction()
    {
        $lists =  Mage::helper("MyBms/MyExtensions")->listMyExtensions();

        $param = $this->getRequest()->getParam('name');
        $release = $this->getRequest()->getParam('release');

        $key = $param.'_'.$release;
        $content = $key."\n"."\n".$lists[$param]['description'];

        $this->_prepareDownloadResponse($key.'_'.'ChangeLog.txt', $content);
    }


    /**
     * Clean doc cache manually
     */
    public function FlushDocumentationCacheAction(){
        Mage::helper("MyBms/Cache")->flushCache(MDN_MyBms_Helper_Data::DOC_CACHE_FILENAME);
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Documentation cache flushed'));
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'MyBms'));
    }

}