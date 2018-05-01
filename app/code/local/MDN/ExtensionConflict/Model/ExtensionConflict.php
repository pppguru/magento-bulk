<?php
/**
 * 
 * 
 */
class MDN_ExtensionConflict_Model_ExtensionConflict  extends Mage_Core_Model_Abstract
{
	private $_rewriteClassesInformation = null;

    CONST MAX_FIX_CONFLICT_DEPTH = 5;
	
	/**
	 * Constructor
	 *
	 */
	public function _construct()
	{
		parent::_construct();
		$this->_init('ExtensionConflict/ExtensionConflict');
	}
		
	
	/**
	 * Check if we can fix conflict (if there are more than 2 rewrite classes, no solution)
	 *
	 */
	public function canFix()
    {
        $canfix = false;
        if ($this->getec_rewrite_classes() != null & strlen($this->getec_rewrite_classes()) > 1) {
            $t = explode(',', $this->getec_rewrite_classes());
            $canfix = (count($t) <= self::MAX_FIX_CONFLICT_DEPTH);
        }
        return $canfix;
	}
	
	/**
	 * Return rewrite class info
	 *
	 * @return unknown
	 */
	public function getRewriteClassesInformation()
	{
        $extHelper = mage::helper('ExtensionConflict/Extension');
		if ($this->_rewriteClassesInformation == null)
		{
			$this->_rewriteClassesInformation = array();
			$t = explode(',', $this->getec_rewrite_classes());
			foreach ($t as $class)
			{
				//collect main information
				$class = trim($class);
				$classArray = array();
				$classArray['class'] = $class;
				$classInfo = explode('_', $class);
				$classArray['editor'] = trim($classInfo[0]);
				$classArray['module'] = trim($classInfo[1]);
				
				//collect config.xml file path
				$classArray['config_file_path'] = $extHelper->getConfigFilePath($classArray['editor'], $classArray['module']);
				
				//collect class path
				$classArray['class_path'] = $extHelper->getClassPath($class);
				
				//collect class declaration
				$classArray['class_declaration'] = $extHelper->getClassDeclaration($class);
				
				//collect new class declaration
				$classArray['new_class_declaration'] = 'class '.$class.' extends ';
				
				$this->_rewriteClassesInformation[] = $classArray;
				
			}
		}
		return $this->_rewriteClassesInformation;
	}

    /*
     *
     */
	/*public function getClassInformation($count)
	{
		$a = $this->getRewriteClassesInformation();
		if ($this->countRewrites() == $count+1)
			return $a[$count-1];
		else 
			return null;
	}*/

    public function getClassInformation($count)
    {
        $a = $this->getRewriteClassesInformation();

        if ($this->countRewrites()-1 >= $count)
            return $a[$count];
        else
            return null;
    }

    public function countRewrites()
    {
        return count($this->getRewriteClassesInformation());
    }
	
	public function realClassCoreName()
	{
		$name = $this->getec_core_class();
		$name = str_replace('models_', '', $name);
		$name = str_replace('helpers_', '', $name);
		return $name;
	}

	public function getId(){
		return $this->getec_id();
	}

}