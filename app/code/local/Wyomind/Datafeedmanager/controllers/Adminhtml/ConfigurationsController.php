<?php

class Wyomind_Datafeedmanager_Adminhtml_ConfigurationsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {

        $this->loadLayout()
                ->_setActiveMenu('catalog/datafeedmanager')
                ->_addBreadcrumb($this->__('Data feed Manager'), ('Data feed Manager'));

        return $this;
    }

    public function indexAction() {

        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('datafeedmanager/configurations')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('datafeedmanager_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('catalog/datafeedmanager')->_addBreadcrumb(Mage::helper('datafeedmanager')->__('Data Feed Manager'), ('Data Feed Manager'));
            $this->_addBreadcrumb(Mage::helper('datafeedmanager')->__('Data Feed Manager'), ('Data Feed Manager'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()
                            ->createBlock('datafeedmanager/adminhtml_configurations_edit'))
                    ->_addLeft($this->getLayout()
                            ->createBlock('datafeedmanager/adminhtml_configurations_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('datafeedmanager')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {

        $this->_forward('edit');
    }

    public function saveAction() {


        if ($data = $this->getRequest()->getPost()) {


            $model = Mage::getModel('datafeedmanager/configurations');

            if ($this->getRequest()->getParam('id')) {
                $model->load($this->getRequest()->getParam('id'));
            }


            $model->setData($data);


            try {

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('datafeedmanager')->__('The data feed configuration has been saved.'));

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('continue')) {
                    $this->getRequest()->setParam('id', $model->getId());
                    $this->_forward('edit');
                    return;
                }


                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('feed_id', $model->getId());
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction() {


        if ($id = $this->getRequest()->getParam('id')) {
            try {

                $model = Mage::getModel('datafeedmanager/configurations');
                $model->setId($id);



                $model->load($id);

                if ($model->getFeedName() && file_exists($model->getPreparedFilename())) {
                    unlink($model->getPreparedFilename());
                }
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('datafeedmanager')->__('The data feed configuration has been deleted.'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('datafeedmanager')->__('Unable to find the data feed configuration to delete.'));

        $this->_redirect('*/*/');
    }

    public function importAction() {

        if ($this->getRequest()->getPost()) {

            if (strtolower(array_pop(explode(".", $_FILES["file"]["name"]))) != "dfm") {
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__("Wrong file type (" . $_FILES["file"]["type"] . ")."));
            } else {// récuperer le contenu}
                $uploader = new Varien_File_Uploader('file');
                $uploader->setAllowedExtensions(array("dfm"));
                $filename = $_FILES["file"]["tmp_name"];
                $handle = fopen($filename, "r");
                if (Mage::getStoreConfig("datafeedmanager/system/trans_domain_export"))
                    $key = "dfm-empty-key";
                else
                    $key = Mage::getStoreConfig("datafeedmanager/license/activation_code");
                $template = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(fread($handle, filesize($filename))), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
                fclose($handle);

                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $dfmc = $resource->getTableName('datafeedmanager_configurations');



                $sql = str_replace("{{datafeedmanager_configurations}}", $dfmc . " ", $template);

                try {
                    $writeConnection->query($sql);
                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("datafeedmanager")->__("The template has been imported."));
                } catch (Mage_Core_Exception $e) {

                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                } catch (Exception $e) {

                    Mage::getSingleton("adminhtml/session")->addError(Mage::helper("datafeedmanager")->__("The template can't be imported.<br/>" . $e->getMessage()));
                }
            }
        }

        $this->loadLayout();
        $this->_setActiveMenu("datafeedmanager/configurations");

        $this->_addContent($this->getLayout()->createBlock("datafeedmanager/adminhtml_import"))
                ->_addLeft($this->getLayout()->createBlock("datafeedmanager/adminhtml_import_edit_tabs"));
        $this->renderLayout();
    }

    public function exportAction() {
        $id = $this->getRequest()->getParam('feed_id');
        $feed = Mage::getModel('datafeedmanager/configurations')->load($id);



        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/force-download')
                ->setHeader('Content-Disposition', 'inline' . '; filename=' . $feed->getFeedName() . ".dfm");
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        foreach ($feed->getData() as $field => $value) {
            $fields[] = $field;
            if ($field == "feed_id")
                $values[] = "NULL";
            else
                $values[] = "'" . str_replace("'", "\'", $value) . "'";
        }
        if (Mage::getStoreConfig("datafeedmanager/system/trans_domain_export"))
            $key = "dfm-empty-key";
        else
            $key = Mage::getStoreConfig("datafeedmanager/license/activation_code");
        $sql = "INSERT INTO {{datafeedmanager_configurations}}(" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ");";
        die(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $sql, MCRYPT_MODE_CBC, md5(md5($key)))));
    }

    public function sampleAction() {


        $id = $this->getRequest()->getParam('feed_id');


        $datafeedmanager = Mage::getModel('datafeedmanager/configurations');
        $datafeedmanager->setId($id);
        $datafeedmanager->_limit = Mage::getStoreConfig("datafeedmanager/system/preview");

        $datafeedmanager->_display = true;


        $datafeedmanager->load($id);
        try {
            $content = $datafeedmanager->generateFile();
            if ($datafeedmanager->_demo) {
                $this->_getSession()->addError(Mage::helper('datafeedmanager')->__("Invalid license."));
                Mage::getConfig()->saveConfig('datafeedmanager/license/activation_code', '', 'default', '0');
                Mage::getConfig()->cleanCache();
                $this->_redirect('*/*/');
            } else
                print($content);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
        } catch (Exception $e) {

            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->addException($e, Mage::helper('datafeedmanager')->__('Unable to generate the data feed.'));
            $this->_redirect('*/*/');
        }
    }

    public function generateAction() {

// init and load datafeedmanager model
        $id = $this->getRequest()->getParam('feed_id');

        $datafeedmanager = Mage::getModel('datafeedmanager/configurations');
        $datafeedmanager->setId($id);
        $limit = $this->getRequest()->getParam('limit');
        $datafeedmanager->_limit = $limit;


// if datafeedmanager record exists
        if ($datafeedmanager->load($id)) {


            try {
                $time_start = time(true);
                $datafeedmanager->generateFile();
                $time_end = time(true);

                $time = $time_end - $time_start;
                if ($time < 60)
                    $time = ceil($time) . ' sec. ';
                else
                    $time = floor($time / 60) . ' min. ' . ($time % 60) . ' sec.';

                $unit = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
                $memory = @round(memory_get_usage() / pow(1024, ($i = floor(log(memory_get_usage(), 1024)))), 2) . ' ' . $unit[$i];

                $ext = array(1 => 'xml', 2 => 'txt', 3 => 'csv', 4 => 'tsv');
                $ext = $ext[$datafeedmanager->getFeed_type()];
                $fileName = preg_replace('/^\//', '', $datafeedmanager->getFeed_path() . $datafeedmanager->getFeed_name() . '.' . $ext);
                $url = (Mage::app()->getStore($datafeedmanager->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $fileName);
                $report = "
                    
                    <table>
                   
                    <tr><td align='right' width='150'>Processing time &#8614; </td><td>$time</td></tr>
                    <tr><td align='right'>Memory usage &#8614; </td><td>$memory</td></tr>
                    <tr><td align='right'>Product inserted &#8614; </td><td>$datafeedmanager->_inc</td></tr>
                    <tr><td align='right'>Generated file &#8614; </td><td><a href='$url' target='_blank'>$url</a></td></tr>
                    </table>";
                if ($datafeedmanager->_demo) {
                    $this->_getSession()->addError(Mage::helper('datafeedmanager')->__("Invalid license."));
                    Mage::getConfig()->saveConfig('datafeedmanager/license/activation_code', '', 'default', '0');
                    Mage::getConfig()->cleanCache();
                } else {
                    $this->_getSession()->addSuccess(Mage::helper('datafeedmanager')->__('The data feed "%s" has been generated.', $datafeedmanager->getFeedName() . '.' . $ext));
                    $this->_getSession()->addSuccess($report);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addException($e, Mage::helper('datafeedmanager')->__('Unable to generate the data feed.'));
            }
        } else {
            $this->_getSession()->addError(Mage::helper('datafeedmanager')->__('Unable to find a data feed to generate.'));
        }

        if ($this->getRequest()->getParam('generate'))
            $this->_redirect('*/*/edit', array("id" => $id));
        else
            $this->_redirect('*/*');
    }

    public function ftpAction() {
        $ftpHost = $this->getRequest()->getParam('ftp_host');
        $ftpLogin = $this->getRequest()->getParam('ftp_login');
        $ftpPassword = $this->getRequest()->getParam('ftp_password');
        $ftpDir = $this->getRequest()->getParam('ftp_dir');
        $useSftp = $this->getRequest()->getParam('use_sftp');
        $ftpActive = $this->getRequest()->getParam('ftp_active');

        if ($useSftp)
            $ftp = new Varien_Io_Sftp();
        else
            $ftp = new Varien_Io_Ftp();

        try {
            $ftp->open(
                    array(
                        'host' => $ftpHost,
                        'user' => $ftpLogin, //ftp
                        'username' => $ftpLogin, //sftp
                        'password' => $ftpPassword,
                        'timeout' => '120',
                        'path' => $ftpDir,
                        'passive' => !($ftpActive)
                    )
            );



            $ftp->write(null, null);
            $ftp->close();



            die("Connection succeeded");
        } catch (Exception $e) {
            die(Mage::helper("datafeedmanager")->__("Ftp error : ") . $e->getMessage());
        }
    }

    public function categoriesAction() {
        $i = 0;
        $io = new Varien_Io_File();
        $realPath = $io->getCleanPath(Mage::getBaseDir() . $this->getRequest()->getParam('file'));
        $io->streamOpen($realPath, "r+");
        while (false !== ($line = $io->streamRead())) {

            if (stripos($line, $this->getRequest()->getParam('s')) !== FALSE)
                echo $line;
        }
        die();
    }

    function libraryAction() {

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $tableEet = $resource->getTableName('eav_entity_type');
        $select = $read->select()->from($tableEet)->where('entity_type_code=\'catalog_product\'');
        $data = $read->fetchAll($select);
        $typeId = $data[0]['entity_type_id'];

        function cmp($a, $b) {

            return ($a['attribute_code'] < $b['attribute_code']) ? -1 : 1;
        }

        /*  Liste des  attributs disponible dans la bdd */

        $attributesList = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($typeId)
                ->addSetInfo()
                ->getData();
        $selectOutput = null;
        $attributesList[] = array("attribute_code" => "qty", "frontend_label" => "Quantity");
        $attributesList[] = array("attribute_code" => "is_in_stock", "frontend_label" => "Is in stock");
        usort($attributesList, "cmp");

        $tabOutput = '<div id="dfm-library"><ul><h3>Attribute groups</h3> ';
        $contentOutput = '<table >';





        $tabOutput .=" <li><a href='#attributes'>Base Attributes</a></li>";


        $contentOutput .="<tr><td><a name='attributes'></a><b>Base Attributes</b></td></tr>";
        foreach ($attributesList as $attribute) {


            if (!empty($attribute['attribute_code']))
                $contentOutput.= "<tr><td>" . $attribute['frontend_label'] . "</td><td><span class='pink'>{" . $attribute['attribute_code'] . "}</span></td></tr>";
        }



        $class = new Wyomind_Datafeedmanager_Model_Configurations;
        $myCustomAttributes = new Wyomind_Datafeedmanager_Model_MyCustomAttributes;
        foreach ($myCustomAttributes->_getAll() as $group => $attributes) {
            $tabOutput .=" <li><a href='#" . $group . "'> " . $group . "</a></li>";
            $contentOutput .="<tr><td><a name='" . $group . "'></a><b>" . $group . "</b></td></tr>";
            foreach ($attributes as $attr) {
                $contentOutput.= "<tr><td><span class='pink'>{" . $attr . "}</span></td></tr>";
            }
        }


        $tabOutput .=" <li><a target='_blank' href='http://wyomind.com/data-feed-manager-magento.html?src=dfm-library&directlink=documentation#Special_attributes'>Special Attributes</a></li>";


        /*

          $myCustomOptions = new MyCustomoptions;
          foreach ($myCustomOptions->_getAll() as $group => $Options) {
          $tabOutput .=" <li><a href='#" . $group . "'> " . $group . "</a></li>";
          $contentOutput .="<tr><td><a name='" . $group . "'></a><b>" . $group . "</b></td></tr>";
          foreach ($Options as $opt) {
          $contentOutput.= "<tr><td><span class='pink'>{attribute_code,<span class='green'>[" . $opt . "]</span>}</span></td></tr>";
          }
          }
         */
        $contentOutput .="</table></div>";
        $tabOutput .= '</ul>';
        die($tabOutput . $contentOutput);
    }

}
