<?php
class Extendware_EWCore_Block_Override_Mage_Adminhtml_Page_Menu extends Extendware_EWCore_Block_Override_Mage_Adminhtml_Page_Menu_Bridge
{
	
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('extendware/ewcore/page/menu.phtml');
    }
    
	protected function _buildMenuArray(Varien_Simplexml_Element $parent=null, $path='', $level=0)
    {
        if (is_null($parent)) {
            $parent = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
        }

        $parentArr = array();
        $sortOrder = 0;
        foreach ($parent->children() as $childName => $child) {
            if (1 == $child->disabled) {
                continue;
            }

            $aclResource = 'admin/' . ($child->resource ? (string)$child->resource : $path . $childName);
            if (!$this->_checkAcl($aclResource)) {
                continue;
            }

            if ($child->depends && !$this->_checkDepends($child->depends)) {
                continue;
            }

            $menuArr = array();

            $menuArr['label'] = $this->_getHelperValue($child);
			$menuArr['style'] = (string)$child->style;
			$menuArr['class'] = (string)$child->class;
            $menuArr['sort_order'] = $child->sort_order ? (int)$child->sort_order : $sortOrder;
			
            if ($child->action) {
                $menuArr['url'] = $this->_url->getUrl((string)$child->action, array('_cache_secret_key' => true));
            } else {
                $menuArr['url'] = '#';
                $menuArr['click'] = 'return false';
            }

            $menuArr['active'] = ($this->getActive()==$path.$childName)
                || (strpos($this->getActive(), $path.$childName.'/')===0);

            $menuArr['level'] = $level;

            if ($child->children) {
            	$fullPath = $path.$childName.'/';
                $menuArr['children'] = $this->_buildMenuArray($child->children, $fullPath, $level+1);
				$parentArr[$childName] = $menuArr;
            } else $parentArr[$childName] = $menuArr;
            

            $sortOrder++;
        }

        uasort($parentArr, array($this, '_sortMenu'));

        while (list($key, $value) = each($parentArr)) {
            $last = $key;
        }
        if (isset($last)) {
            $parentArr[$last]['last'] = true;
        }

        return $parentArr;
    }
    
	public function getMenuArray()
    {
    	$menu = $this->_buildMenuArray();
    	$quickaccess = null;
    	if (isset($menu['ewcore']['children']['quickaccess'])) {
    		$quickaccess =& $menu['ewcore']['children']['quickaccess'];
    		if (!isset($quickaccess['children']) or !is_array($quickaccess['children'])) {
    			$quickaccess['children'] = array();
    		}
    		foreach ($quickaccess['children'] as $key => $info) {
    			if (strpos($key, 'ew') ===0) {
    				unset($quickaccess['children'][$key]);
    			}
    		}
    	}
    	
    	if ($quickaccess === null) {
    		return $menu;
    	}
    	
		$modules = array();
		$collection = Mage::getSingleton('ewcore/module')->getCollection();
		foreach ($collection as $module) {
			if ($module->isActive() === false) continue;
			if ($module->isExtendware() === false) continue;
			$key = $module->getFriendlyName();
			if (isset($modules[$key])) {
				$key .= $module->getId();
			}
			$modules[$key] = $module;
		}
		
		ksort($modules);
		
		foreach ($modules as $module) {
			$moduleKey = strtolower($module->getName());
			
			if ($module->isForMainsite() === false) {
				$moduleMenu = $this->_buildModuleMenuArray($module, 'ewcore/quickaccess', 2);
				if (is_array($moduleMenu)) {
					$quickaccess['children'] = array_merge($quickaccess['children'], $moduleMenu);
				}
			} else {
				$moduleMenu = $this->_buildModuleMenuArray($module, 'ewcore/quickaccess', 3);
				if (is_array($moduleMenu)) {
					$quickaccess['children']['mainsite']['children'] = array_merge($quickaccess['children']['mainsite']['children'], $moduleMenu);
				}
			}
		}
		
		if (isset($quickaccess['children']) and is_array($quickaccess['children'])) {
			$count = count($quickaccess['children']);
			foreach ($quickaccess['children'] as &$item) {
				if (--$count) unset($item['last']);
				else $item['last'] = 1;
				unset($item); // cleanup reference
			}
			
			if (isset($quickaccess['children']['mainsite']['children']) and is_array($quickaccess['children']['mainsite']['children'])) {
				$count = count($quickaccess['children']['mainsite']['children']);
				foreach ($quickaccess['children']['mainsite']['children'] as &$item) {
					if (--$count) unset($item['last']);
					else $item['last'] = 1;
					unset($item); // cleanup reference
				}
			}
			
			if (empty($quickaccess['children']['mainsite']) === false) {
				unset($quickaccess['children']['mainsite']['last']);
				$menu['system']['children'] = array('mainsite' => $quickaccess['children']['mainsite']) + $menu['system']['children'];
				unset($quickaccess['children']['mainsite']);
			}
		}
		
		$location = Mage::helper('ewcore/config')->getAdminMenuLocation();
		if ($location and @isset($menu[$location]['children'])) {
			$menu[$location]['children'] = array('ewcore' => $menu['ewcore']) + $menu[$location]['children'];
			unset($menu['ewcore']);
		}
        return $menu;
    }

    protected function _buildModuleMenuArray(Extendware_EWCore_Model_Module_Item $module, $path = '', $level = 0)
    {
    	$moduleKey = strtolower($module->getName());
       	$parent = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('extendware/quickaccess');
		
        $parentArr = array($moduleKey => array('children' => array()));
        $sortOrder = 0;
        if ($parent) {
	        foreach ($parent->children() as $childName => $child) {
	        	if ($childName != $moduleKey) continue;
	 			$result = $this->_buildModuleMenuChild($childName, $child, $path, $level, $sortOrder);
	 			if ($result) {
	 				$parentArr[$moduleKey] = $result;
	 				$sortOrder++;
	 			}
	            break;
	        }
        }
        
        $configureUrl = (string)$module->getConfigureRoute();
        if ($configureUrl) {
        	$aclResource = 'admin/extendware/' . $moduleKey . '/settings';
            if ($this->_checkAcl($aclResource)) {
	        	$parentArr[$moduleKey]['children']['configure'] = array(
	        			'label' => $this->__('Configure '), // trailing space added due to translation strings in norwegian translation files
	        			'url' => $this->_url->getUrl($configureUrl, array('_cache_secret_key' => true)),
	        			'sort_order' => 0,
	        			'class' => 'ewcore-configure',
	        			'active' => true,
	        			'level' => $level+1
	        	);
	        
		        $count = count($parentArr[$moduleKey]['children']);
		        foreach ($parentArr[$moduleKey]['children'] as &$item) {
		    		if (--$count) unset($item['last']);
		    		else $item['last'] = 1;
		    	}
            }
        }
        
        if (count($parentArr[$moduleKey]['children'])) {
        	$parentArr[$moduleKey]['url'] = '#';
        	$parentArr[$moduleKey]['click'] = 'return false';
	    	if (empty($parentArr[$moduleKey]['label'])) {
				$parentArr[$moduleKey]['label'] = $module->getFriendlyName();
			}
        }
        		
        return count($parentArr[$moduleKey]['children']) ? $parentArr : false;
    }
    
	protected function _buildModuleMenuChildrenArray(Varien_Simplexml_Element $parent=null, $path='', $level=0)
    {
        $parentArr = array();
        $sortOrder = 0;
        foreach ($parent->children() as $childName => $child) {
 			$result = $this->_buildModuleMenuChild($childName, $child, $path, $level, $sortOrder);
 			if ($result) {
 				$parentArr[$childName] = $result;
 				$sortOrder++;
 			}
        }

        uasort($parentArr, array($this, '_sortMenu'));

        while (list($key, $value) = each($parentArr)) {
            $last = $key;
        }
        if (isset($last)) {
            $parentArr[$last]['last'] = true;
        }

        return $parentArr;
    }
    
	protected function _buildModuleMenuChild($childName, $child, $path, $level, $sortOrder = 0) {
    	if ($child->inherit) {
    		$inheritPath = (string)$child->inherit;
			$copy = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode($inheritPath);
			if ($copy) {
				foreach ($child->children() as $k => $v) {
					if ($k == 'inherit') continue;
					$copy->extendChild($v, true);
				}
				
				$child = $copy;
			}
		}
		
		if (1 == $child->disabled) {
			return false;
		}
		
		$aclResource = 'admin/' . ($child->resource ? (string)$child->resource : $path . $childName);
		if (!$this->_checkAcl($aclResource)) {
			return false;
		}
		
		if ($child->depends && !$this->_checkDepends($child->depends)) {
			return false;
		}
		
		$menuArr = array();
		
		$menuArr['label'] = $this->_getHelperValue($child);
		
		$menuArr['sort_order'] = $child->sort_order ? (int)$child->sort_order : $sortOrder;
		
		if ($child->action) {
			$menuArr['url'] = $this->_url->getUrl((string)$child->action, array('_cache_secret_key' => true));
		} else {
			$menuArr['url'] = '#';
			$menuArr['click'] = 'return false';
		}
		
		$menuArr['active'] = ($this->getActive()==$path.$childName)
			|| (strpos($this->getActive(), $path.$childName.'/')===0);
		
		$menuArr['level'] = $level;
		
		if ($child->children) {
			$menuArr['children'] = $this->_buildModuleMenuChildrenArray($child->children, $path.$childName.'/', $level+1);
		}
            
		return $menuArr;
    }
}
