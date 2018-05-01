<?php

/*
 * Created on Jun 26, 2008
 *
 */

class MDN_Orderpreparation_Adminhtml_OrderPreparation_CarrierTemplateController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Carrier templates'));

        $this->renderLayout();
    }

    public function indexAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Carrier templates'));

        $this->renderLayout();
    }

    public function NewAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New carrier templates'));

        $this->renderLayout();
    }

    public function EditAction() {
        $this->loadLayout();

        $templateId = $this->getRequest()->getParam('ct_id');

        $this->getLayout()->getBlock('fields_export')->setType('export')->setTemplateId($templateId);
        $this->getLayout()->getBlock('fields_import')->setType('import')->setTemplateId($templateId);

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit carrier templates #'.$templateId));

        $this->renderLayout();
    }

    public function CreateTemplateAction() {
        $name = $this->getRequest()->getPost('ct_name');

        $obj = mage::getModel('Orderpreparation/CarrierTemplate')
                        ->setct_name($name)
                        ->save();

        $this->_redirect('adminhtml/OrderPreparation_CarrierTemplate/Edit', array('ct_id' => $obj->getId()));
    }

    public function SaveAction() {
        //retrieve information
        $templateId = $this->getRequest()->getPost('ct_id');
        $data = $this->getRequest()->getPost('data');
        $carrierTemplate = mage::getModel('Orderpreparation/CarrierTemplate')->load($templateId);

        //manage checkboxes
        if (!isset($data['ct_export_add_header']))
            $data['ct_export_add_header'] = '0';
        if (!isset($data['ct_export_remove_accent']))
            $data['ct_export_remove_accent'] = '0';
        if (!isset($data['ct_export_convert_utf8']))
            $data['ct_export_convert_utf8'] = '0';
        if (!isset($data['ct_import_skip_first_record']))
            $data['ct_import_skip_first_record'] = '0';

        //save information
        if ($carrierTemplate->getId()) {
            foreach ($data as $key => $value)
                $carrierTemplate->setData($key, $value);
            $carrierTemplate->save();
        }

        //save fields
        $fieldsData = $this->getRequest()->getPost('fields');
        foreach ($carrierTemplate->getFields() as $field) {
            $id = $field->getId();
            if (isset($fieldsData[$id]['delete']))
                $field->delete();
            else {
                foreach ($fieldsData[$id] as $key => $value)
                    $field->setData($key, $value);
                $field->save();
            }
        }

        //create new import fields
        $newImportField = $this->getRequest()->getPost('fields');
        $newImportField = $newImportField['new_import'];
        if ($newImportField['ctf_name'] != '') {
            $obj = mage::getModel('Orderpreparation/CarrierTemplateField');
            foreach ($newImportField as $key => $value)
                $obj->setData($key, $value);
            $obj->setctf_type('import');
            $obj->setctf_template_id($templateId);
            $obj->save();
        }

        //create new export field
        $newExportField = $this->getRequest()->getPost('fields');
        $newExportField = $newExportField['new_export'];
        if ($newExportField['ctf_name'] != '') {
            $obj = mage::getModel('Orderpreparation/CarrierTemplateField');
            foreach ($newExportField as $key => $value)
                $obj->setData($key, $value);
            $obj->setctf_type('export');
            $obj->setctf_template_id($templateId);
            $obj->save();
        }


        //confirm & redirect
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
        $this->_redirect('*/*/Edit', array('ct_id' => $templateId));
    }

    /**
     * Create an xml file with template structure
     *
     */
    public function ExportAction() {
        $templateId = $this->getRequest()->getParam('ct_id');
        $template = mage::getModel('Orderpreparation/CarrierTemplate')->load($templateId);
        $content = mage::helper('Orderpreparation/XmlCarrierTemplate')->export($template);

        $fileName = $template->getct_name() . '.xml';

        $this->_prepareDownloadResponse($fileName, $content, 'application/xml');
    }

    /**
     * Method to download file on client side
     *
     * @param unknown_type $fileName
     * @param unknown_type $content
     * @param unknown_type $contentType
     * @param unknown_type $contentLength
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null) {
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', strlen($content))
                ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
                ->setBody($content);
    }



    /**
     * Import template using xml file
     *
     */
    public function ImportTemplateAction() {
        try {
            //save text file
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('xml'));
            $path = Mage::app()->getConfig()->getTempVarDir() . '/import/';
            $uploader->save($path);

            if ($uploadFile = $uploader->getUploadedFileName()) {
                $filePath = $path . $uploadFile;
                $template = mage::helper('Orderpreparation/XmlCarrierTemplate')->import($filePath);

                Mage::getSingleton('adminhtml/session')->addSuccess('Template imported');
                $this->_redirect('*/*/Edit', array('ct_id' => $template->getId()));
            }
            else
                throw new Exception('Unable to get file name');
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            $this->_redirect('*/*/New');
        }
    }

    /**
     * Delete template
     *
     */
    public function DeleteAction() {
        $templateId = $this->getRequest()->getParam('ct_id');
        $template = mage::getModel('Orderpreparation/CarrierTemplate')->load($templateId);
        if ($template->getId()) {
            foreach ($template->getFields() as $field)
                $field->delete();

            $template->delete();
        }

        Mage::getSingleton('adminhtml/session')->addSuccess('Template deleted');
        $this->_redirect('*/*/Grid');
    }

    /**
     * Form to import tracking files
     *
     */
    public function ImportTrackingAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Tracking'));

        $this->renderLayout();
    }

    /**
     * Process tracking file
     *
     */
    public function SubmitTrackingFileAction() {

        //import tracking could be long
        set_time_limit(600);
        ini_set('max_execution_time', 600);

        //init
        $ct_id = $this->getRequest()->getPost('ct_id');
        $template = mage::getModel('Orderpreparation/CarrierTemplate')->load($ct_id);

        try {
            //save text file
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('txt', 'csv', 'xml'));
            $path = Mage::app()->getConfig()->getTempVarDir() . '/import/';
            $uploader->save($path);

            //check
            if ($uploadFile = $uploader->getUploadedFileName()) {
                //import file
                $filePath = $path . $uploadFile;
                $t_lines = file($filePath);
                $result = $template->importTrackingFile($t_lines);

                //display result
                Mage::getSingleton('adminhtml/session')->addSuccess($result);
                $this->_redirect('*/*/ImportTracking');
            }
            else
                throw new Exception($this->__('Unable to upload file'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
            $this->_redirect('*/*/ImportTracking');
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation');
    }

}