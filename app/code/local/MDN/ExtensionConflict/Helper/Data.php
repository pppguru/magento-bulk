<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2015 BoostMyShop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_ExtensionConflict
 */
class MDN_ExtensionConflict_Helper_Data extends Mage_Core_Helper_Abstract {

    private $_moduleList;


    /**
     * Refresh list
     *
     */
    public function RefreshList() {
        //truncate table
        Mage::getResourceModel('ExtensionConflict/ExtensionConflict')->TruncateTable();

        //retrieve all config.xml
        $tConfigFiles = $this->getConfigFilesList();

        //parse all config.xml
        $rewrites = array();
        foreach ($tConfigFiles as $configFile) {
            $rewrites = $this->getRewriteForFile($configFile, $rewrites);
        }

        //insert in database
        foreach ($rewrites as $key => $value) {
            $t = explode('/', $key);
            $moduleName = $t[0];
            $className = $t[1];

            $record = mage::getModel('ExtensionConflict/ExtensionConflict');
            $record->setec_core_module($moduleName);
            $record->setec_core_class($className);

            $rewriteClasses = join(', ', $value);
            $record->setec_rewrite_classes($rewriteClasses);

            if (count($value) > 1)
                $record->setec_is_conflict(1);

            $record->save();
        }
    }

    /**
     * create an array with all config.xml files
     *
     */
    public function getConfigFilesList() {
        $buffer = array();
        $codePath = Mage::getBaseDir().DS.'app'.DS.'code';

        $tmpPath = Mage::app()->getConfig()->getTempVarDir() . '/ExtensionConflict/';
        if (!is_dir($tmpPath))
            mkdir($tmpPath);

        $locations = array();
        $locations[] = $codePath . '/local/';
        $locations[] = $codePath . '/community/';
        $locations[] = $tmpPath;

        foreach ($locations as $location) {
            //parse every sub folders (means extension folders)
            $poolDir = opendir($location);
            while ($namespaceName = readdir($poolDir)) {
                if (!$this->directoryIsValid($namespaceName))
                    continue;

                //parse modules within namespace
                $namespacePath = $location . $namespaceName . '/';
                $namespaceDir = opendir($namespacePath);
                while ($moduleName = readdir($namespaceDir)) {
                    if (!$this->directoryIsValid($moduleName))
                        continue;


                    //check if it is a real active module and not backup badly nammed of a module
                    $key = $namespaceName.'_'.$moduleName;
                    if ($this->checkModulePresence($key)) {
                        $modulePath = $namespacePath . $moduleName . '/';
                        $configXmlPath = $modulePath . 'etc/config.xml';

                        if (file_exists($configXmlPath))
                            $buffer[] = $configXmlPath;
                    }
                }
                closedir($namespaceDir);
            }
            closedir($poolDir);
        }

        return $buffer;
    }

    /**
     *
     *
     * @param unknown_type $dirName
     * @return unknown
     */
    private function directoryIsValid($dirName) {
        switch ($dirName) {
            case '.':
            case '..':
            case '.DS_Store':
            case '':
                return false;
                break;
            default:
                return true;
                break;
        }
    }

    private function manageModule($moduleName) {
        switch ($moduleName) {
            case 'global':
                return false;
                break;
            default:
                return true;
                break;
        }

    }

    public function getAllowedNodeTypes(){
        return  array('blocks', 'models', 'helpers');
    }

    /**
     * Return all rewrites for a config.xml
     *
     * @param unknown_type $configFilePath
     * @param unknown_type $results
     */
    public function getRewriteForFile($configFilePath, $results) {
        try {
            //load xml
            $xmlContent = file_get_contents($configFilePath);
            $domDocument = new DOMDocument();
            $domDocument->loadXML($xmlContent);

            //parse every node types
            $nodeTypes = $this->getAllowedNodeTypes();

            foreach ($nodeTypes as $nodeType) {
                if (!$domDocument->documentElement)
                    continue;

                foreach ($domDocument->documentElement->getElementsByTagName($nodeType) as $nodeTypeMarkup) {
                    foreach ($nodeTypeMarkup->getElementsByTagName('rewrite') as $markup) {
                        //parse child nodes
                        $moduleName = $markup->parentNode->tagName;
                        if ($this->manageModule($moduleName)) {
                            foreach ($markup->getElementsByTagName('*') as $childNode) {
                                //get information
                                $className = $nodeType . '_' . $childNode->tagName;
                                $rewriteClass = $childNode->nodeValue;

                                if ($this->checkModulePresence($this->getModuleKeyName($rewriteClass))) {
                                    //add to result
                                    $key = $moduleName . '/' . $className;
                                    if (!isset($results[$key]))
                                        $results[$key] = array();
                                    $results[$key][] = $rewriteClass;
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            return $results;
        }

        return $results;
    }

    public function getModuleKeyName($rewriteClass){
        $moduleKeyName = '';
        $array = explode('_',$rewriteClass);
        if(count($array)>2){
            $moduleKeyName = $array[0].'_'.$array[1];
        }
        return $moduleKeyName;
    }
    /**
     *
     * Checks if a module is present
     */
    public function checkModulePresence($moduleKeyName)
    {
        $presentAndActive = false;

        if ($moduleKeyName) {
            if (!$this->_moduleList) {
                $this->_moduleList = Mage::getConfig()->getNode('modules')->children();
            }

            //echo Mage::helper('CrmTicket/String')->getVarDumpInString($this->_moduleList);

            if (array_key_exists($moduleKeyName, $this->_moduleList)) {
                $moduleConf = $this->_moduleList->$moduleKeyName;

                //$moduleConf is a Mage_Core_Model_Config_Element
                if ($moduleConf->is("active", "true")) {
                    $presentAndActive = true;
                }
            }
        }
        return $presentAndActive;
    }

    public function checkCachesAndCompiler() {
        $problems = array();

        //Check magento Compiler
        if (defined('COMPILER_INCLUDE_PATH')) {
            $problems[] = $this->__('Compiler is ACTIVE');
        }

        //APC cache
        if(extension_loaded('apc') && ini_get('apc.enabled')){
            $problems[] = $this->__('APC cache is PRESENT');
        }

        //Mem cache
        if (class_exists('Memcache',false)) {
            $problems[] = $this->__('Memcache is PRESENT');
        }
        return $problems;
    }

}
