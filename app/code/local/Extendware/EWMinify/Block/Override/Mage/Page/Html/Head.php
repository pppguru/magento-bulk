<?php
class Extendware_EWMinify_Block_Override_Mage_Page_Html_Head extends Extendware_EWMinify_Block_Override_Mage_Page_Html_Head_Bridge
{
	static protected $checkFilemtime = false;
	protected $canSaveCache = true;
	
    public function __construct()
    {
        parent::__construct();
        self::$checkFilemtime = Mage::helper('ewminify/config')->isFilemtimeEnabled();
        $this->setMinifyCacheDirectory(Mage::getConfig()->getOptions()->getMediaDir() . DS . Mage::helper('ewminify/config')->getSlugCustomPath() . DS . 'files');
        $this->setBaseMinifyUrl();
        $this->setBaseJsUrl(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS));
    }
    
    protected function getBaseMinifyUrl($type) {
    	static $cache = array();
    	if (isset($cache[$type]) === false) {
	    	if (Mage::helper('ewminify/config')->isRewritesEnabled() === true) {
	    		$cache[$type] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . Mage::helper('ewminify/config')->getSlugCustomPath() . '/';
	    		$cache[$type] = Mage::helper('ewminify')->rewriteUrl('header_resource', $cache[$type]);
	    	} else {
	    		$cache[$type] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . Mage::helper('ewminify/config')->getSlugCustomPath() . '/files/' . $type . '/';
	    		$cache[$type] = Mage::helper('ewminify')->rewriteUrl('header_resource', $cache[$type]);
	    	}
    	}
    	
    	return $cache[$type];
    }
    
	protected function setMinifyCacheDirectory($directory) {
    	if (Mage::getConfig()->getOptions()->createDirIfNotExists($directory) === false) {
    		echo $this->__('Could not create directory %s. Please ensure that parent directories have permissions 0777.', $directory);
    		exit;
    	}
    	return $this->setData('minify_cache_directory', $directory);
    }
    
    public function isEnabledForJs() {
    	return Mage::helper('ewminify')->requestMatchesUrlFilters() === false and ($this->mergeJs() !== false or $this->getJsCompressor() != 'none' or Mage::helper('ewminify')->isDeferJsEnabled() === true);
    }
    
	public function isEnabledForCss() {
    	return Mage::helper('ewminify')->requestMatchesUrlFilters() === false and ($this->mergeCss() !== false or $this->getCssCompressor() != 'none');
    }
    
	public function mergeJs()
    {
    	static $res = -1;
    	if ($res === -1) {
	    	static $options = array(
	    						'always' => true,
	    						'automatic' => 'automatic',
	    						'automatic_aggressive' => 'automatic_aggressive',
	    						'never' => false
	    	);
	    	$key = Mage::getStoreConfig('ewminify_files/' . $this->getArea() . '_files/merge_js');
    		$res = array_key_exists($key, $options) ? $options[$key] : false;
    	}
    	
    	return $res;
    }
    
    public function mergeCss()
    {
    	static $res = -1;
    	if ($res === -1) {
	    	static $options = array(
	    						'always' => true,
	    						'automatic' => 'automatic',
	    						'automatic_aggressive' => 'automatic_aggressive',
	    						'never' => false
	    	);
	    	$key = Mage::getStoreConfig('ewminify_files/' . $this->getArea() . '_files/merge_css');
	    	$res = array_key_exists($key, $options) ? $options[$key] : false;
    	}
    	
    	return $res;
    }
    
	public function getCssCompressor()
	{
		static $res = null;
		if ($res === null) $res = Mage::getStoreConfig('ewminify_files/' . $this->getArea() . '_files/css_compressor');
		return $res;
	}
	
	public function getJsCompressor()
	{
		static $res = null;
		if ($res === null) $res = Mage::getStoreConfig('ewminify_files/' . $this->getArea() . '_files/js_compressor');
		return $res;
	}
	
	public function getArea()
	{
		if ($this instanceof Mage_Adminhtml_Block_Page_Head) {
			return 'adminhtml';
		}
		
		return 'frontend';
	}
	
	public function getCacheArea()
	{
		return $this->getArea();
	}
	
	public function addItem($type, $name, $params=null, $if=null, $cond=null) {
    	if (!trim($name)) return $this;
        return parent::addItem($type, $name, $params, $if, $cond);
    }
    
	static public function removeObject(&$data, $key) {
		if (is_object($data) === true) {
			$data = 'object';
		}
	}
	public function getCssJsHtml()
	{
		self::createHtaccessFile();

		$cacheKey = null;
		if (Mage::app()->useCache('block_html')) {
			$dataForKey = $this->_data;
			array_walk_recursive($dataForKey, array(&$this, 'removeObject'));
			$cacheKey = md5(Mage::app()->getStore()->getId() . '-' . Mage::helper('ewcore')->getRequestRoute() . ' - ' . serialize($dataForKey)  . '-' . (int)Mage::app()->getRequest()->isSecure());
			$html = Mage::app()->loadCache($cacheKey);
			if ($html) {
				return $html;
			}
		}
		
		$html = '';
		$script = '<script type="text/javascript" src="%s" %s></script>';
		$stylesheet = '<link type="text/css" rel="stylesheet" href="%s" %s/>';
		$alternate = '<link rel="alternate" type="%s" href="%s" %s/>';
		$linkrel = '<link%s href="%s" />';
		
		if (Mage::helper('ewminify')->isDeferJsEnabled()) {
			$script = '<script type="text/javascript">ewminifyAddScript("%s");</script>';
			$code = '
				var ewminifyCallback = function() {
					if (!ewminifyScripts.length) {
						if(typeof(ewminifyAfterScripts) == "function") ewminifyAfterScripts();
						document.fire("ewminify:dom:loaded");
						document.fire("ewminify:window:load");
					} else ewminifyLoadNextScript();
				};
				
				var ewminifyScripts = [];
				function ewminifyLoadNextScript() {
					if (typeof(ewminifyBeforeScripts) == "function") ewminifyBeforeScripts();
					if (ewminifyScripts.length <= 0) return;
					var url = ewminifyScripts.splice(0, 1);
				    var script = document.createElement("script");
				    script.type = "text/javascript";
					script.async = true;
					
					script.src = url;
				    if (script.readyState) {
				        script.onreadystatechange = function() {
				            if (script.readyState == "loaded" || script.readyState == "complete") {
				                script.onreadystatechange = null;
				                ewminifyCallback();
				            }
				        };
				    } else {
				        script.onload = function(){
				            ewminifyCallback();
				        };
				    }
				
				    parentGuest = document.getElementsByTagName("body")[0];
				    if (parentGuest.nextSibling) parentGuest.parentNode.insertBefore(script, parentGuest.nextSibling);
				    else parentGuest.parentNode.appendChild(script);
				}

				function ewminifyAddScript(u) {
					ewminifyScripts.push(u);
				}
					
				function ewminifyAddInlineScript(content) {
					var script = document.createElement("script");
					script.text = content;
					document.getElementsByTagName("head")[0].appendChild(script);
				}

				if (window.addEventListener) window.addEventListener("load", ewminifyLoadNextScript, false);
				else if (window.attachEvent) window.attachEvent("onload", ewminifyLoadNextScript);
				else window.onload = ewminifyLoadNextScript;			
			';
			$code = Extendware_EWMinify_Model_Minify::js($code, array(), 'jsminplus');
			$html .= '<script type="text/javascript">' . $code . '</script>';
		}
		
		$types = array('js' => array(), 'skin_js' => array(), 'js_css' => array(), 'skin_css' => array(), 'rss' => array(), 'link_rel' => array(), 'external_js' => array(), 'external_css' => array());
		foreach ($this->_data['items'] as $item) {
			if (!is_null($item['cond']) && !$this->getData($item['cond'])) {
				continue;
			}
			
			$if = !empty($item['if']) ? $item['if'] : '';
			switch ($item['type']) {
				case 'js':
					$types[$item['type']][$if]['script'][] = array(
													'file' => $item['name'],
													'params' => (string) $item['params'],
													'url' => $this->rewriteUrlsInString($this->getBaseJsUrl().$item['name']),
													'filepath' => isset($item['filepath']) ? $item['filepath'] : null,
													'original' => $item
		                                        );
					break;
				case 'skin_js':
					$types[$item['type']][$if]['script'][] = array(
														'file' => $this->getSkinRelativeFilePath($item['name']),
														'params' => (string) $item['params'],
														'url' => $this->rewriteUrlsInString($this->getSkinUrl($item['name'])),
														'filepath' => isset($item['filepath']) ? $item['filepath'] : null,
				    									'original' => $item
			                                        );
					break;
				case 'js_css':
				    $types[$item['type']][$if]['stylesheet'][] = array(
    				                                'file' => $item['name'],
    				                                'params' => (string) $item['params'],
				    								'url' => $this->rewriteUrlsInString($this->getBaseJsUrl().$item['name']),
				    								'filepath' => isset($item['filepath']) ? $item['filepath'] : null,
				    								'original' => $item
    				                               );
					break;
				case 'skin_css':
				    $types[$item['type']][$if]['stylesheet'][] = array(
															'file' => $this->getSkinRelativeFilePath($item['name']),
															'params' => (string) $item['params'],
				    										'url' => $this->rewriteUrlsInString($this->getSkinUrl($item['name'])),
				    										'filepath' => isset($item['filepath']) ? $item['filepath'] : null,
				    										'original' => $item
				                                        );
					break;
				case 'rss':
					$types[$item['type']][$if]['rss'][] = array(
													'url' => $item['name'],
					                                'params' => (string) $item['params'],
													'filepath' => isset($item['filepath']) ? $item['filepath'] : null,
													'original' => $item
					                             );
					break;
				case 'link_rel':
					$types[$item['type']][$if]['link_rel'][] = array(
													'url' => $item['name'],
					                                'params' => (string) $item['params'],
													'original' => $item
					                             );
					break;
				case 'external_js':
					$types[$item['type']][$if]['script'][] = array(
						'type' => 'external',
						'url' => $item['name'],
						'params' => (string) $item['params']
					);
					break;
				case 'external_css':
					$types[$item['type']][$if]['stylesheet'][] = array(
						'type' => 'external',
						'url' => $item['name'],
						'params' => (string) $item['params']
					);
					break;
			}
		}
	
		$lines = array();
		foreach ($types as $type => $data) {
			foreach ($data as $if => $groups) {
				foreach ($groups as $class => $group) {
					if (isset($lines[$if][$class]) === false) {
						$lines[$if][$class] = array();
					}
					foreach ($group as $item) {
						if (isset($lines[$if][$class][$item['params']]) === false) {
							$lines[$if][$class][$item['params']] = array();
						}
						$lines[$if][$class][$item['params']][] = $item;
					}
				}
			}
		}
	
		foreach ($lines as $if => $items) {
			if (!empty($if)) {
                // open !IE conditional using raw value
				if (strpos($if, "><!-->") !== false) {
					$html .= $if . "\n";
				} else {
					$html .= '<!--[if '.$if.']>' . "\n";
				}
			}
			
			if (!empty($items['stylesheet'])) {
				foreach ($items['stylesheet'] as $params => $item) {
				    $resources = self::getStylesheetResourcesFromData($item);
				    foreach ($resources as $resource) {
	                    $html .= sprintf($stylesheet, $resource['url'], $params);
	                }
				}
			}
            
			if (!empty($items['script'])) {
				foreach ($items['script'] as $params => $item) {
				    $resources = self::getScriptResourcesFromData($item);
				    foreach ($resources as $resource) {
	                    $html .= sprintf($script, $resource['url'], $params);
	                }
				}
			}

			if (!empty($items['rss'])) {
				foreach ($items['rss'] as $params => $item) {
				    $resources = self::getRssResourcesFromData($item);
				    foreach ($resources as $resource) {
	                    $html .= sprintf($alternate, $resource['type'], $resource['url'], $params);
	                }
				}
			}
			
			
			if (!empty($items['link_rel'])) {
				foreach ($items['link_rel'] as $params => $item) {
				    $resources = self::getLinkRelFromData($item);
				    foreach ($resources as $resource) {
	                    $html .= sprintf($linkrel, $resource['params'], $resource['url']);
	                }
				}
			}
			
			if (!empty($if)) {
				$html .= '<![endif]-->' . "\n";
			}
		}

		if ($cacheKey !== null and $this->canSaveCache) {
			Mage::app()->saveCache($html, $cacheKey, array('block_html'));
		}
		return $html;
	}
	
	protected function breakResourceDataIntoGroups(array $data = array(), $merge = false) {
		static $groups = null;
		static $ignoreOrderKeyList = array('js#varien/configurable.js');

		if ($merge === true) { // merge everything
			$dataGroups = array();
			$filters = Mage::helper('ewminify/config')->getFileMergeExceptions();
			$index = 0;
			foreach ($data as $item) {
				$added = false;
				foreach ($filters as $filter) {
					if (strpos($item['file'], $filter) !== false) {
						if (empty($dataGroups) === false) $index++;
						if (isset($dataGroups[$index]) === false) $dataGroups[$index] = array();
						$dataGroups[$index][] = $item;
						$index++;
						$added = true;
						break;
					}
				}
				if ($added) continue;
				$dataGroups[$index][] = $item;
			}
			return $dataGroups;
		} elseif ($merge === false) { // merge nothing
			$dataGroups = array();
			foreach ($data as $item) {
				$dataGroups[] = array($item);
			}
			return $dataGroups;
		}
		
		// this will perform a smart merge, which is the preferred choice
		if (is_array($groups) === false) {
			$groups = array();
			$filters = Mage::helper('ewminify/config')->getFileMergeExceptions();

			$blockSearch1 = (array)$this->getLayout()->getXpath('//block[@type="page/html_head"]');
			$blockSearch2 = (array)$this->getLayout()->getXpath('//reference[@name="head"]');

			$blocks = array_merge($blockSearch1, $blockSearch2);
			$count = 0;
			foreach ($blocks as $block) {	
				if (empty($block)) continue;
				$block = (array)$block;
				if (!isset($block['action'])) continue;
				if(!is_array($block['action'])) $block['action'] = array($block['action']);
				$count++;
				$defaultGroup = @$block['@attributes']['_ewminify_default_group'];
				if (!$defaultGroup) $defaultGroup = '____' . $count;

				foreach ($block['action'] as $action) {
					$action = (array)$action;
					$method = isset($action['@attributes']['method']) ? $action['@attributes']['method'] : null;
					$ignoreOrder = (bool)(isset($action['@attributes']['ewminify_ignore_order']) ? $action['@attributes']['ewminify_ignore_order'] : false);
					$file = @(isset($action['name']) ? $action['name'] : (isset($action['script']) ? $action['script'] : (isset($action['stylesheet']) ? $action['stylesheet'] : null)));
					$type = isset($action['type']) ? $action['type'] : null;
					$group = isset($action['@attributes']['ewminify_group']) ? $action['@attributes']['ewminify_group'] : null;
					$skipGroup = (bool)(isset($action['@attributes']['_ewminify_skip_group']) ? $action['@attributes']['_ewminify_skip_group'] : false);
					if (!$file or $skipGroup) continue;
					
					// the need for this is not needed when we remove unicode characters
					if (strpos($file, '.min.js') !== false) {
						$group = $file;
					} elseif (strpos($file, 'jquery') !== false) {
						$group = $file;
					}
					
					foreach ($filters as $filter) {
						if (strpos($file, $filter) !== false) {
							$group = $file;
							break;
						}
					}
					
					$realType = '';
					if ($method == 'addCss') $realType = 'skin_css';
					elseif ($method == 'addJs') $realType = 'js';
					elseif ($method == 'addItem' and $type) $realType = $type;
					
					if ($group or isset($groups[$realType . '#' . $file]) === false) {
						$groups[$realType . '#' . $file] = $group ? $group : $defaultGroup;
					}
					
					if ($ignoreOrder === true) {
						$ignoreOrderKeyList[] = $realType . '#' . $file;
					}
				}
			}
		}

		$lastIndex = null;
		$indexLog = array();
		$dataGroups = array();
		$isSafe = (bool)($merge == 'automatic');
		foreach ($data as $item) {
			// get the very first item data as this will contain the type and key
			// that was found in the XML that we used to create the groups
			$original = $item;
			while (isset($original['original']) and is_array($original['original'])) {
				$original = $original['original'];
			}
			
			$key = @($original['type'] . '#' . $original['name']);
			$index = isset($groups[$key]) ? $groups[$key] : $key;
			if (isset($indexLog[$index]) === false) {
				$indexLog[$index] = 0;
			}
			
			if ($isSafe === true and $index != $lastIndex) {
				if (in_array($key, $ignoreOrderKeyList) === false) {
					if ($lastIndex !== null) {
						$indexLog[$lastIndex]++;
					}
				}
				$lastIndex = $index;
			}
			
			$index .= '_' . (int)$indexLog[$index];
			if (isset($dataGroups[$index]) == false) {
				$dataGroups[$index] = array();
			}

			$dataGroups[$index][] = $item;
		}
		
		/*foreach ($dataGroups as $name => $files) {
			echo "<div align=left style='background: white'>$name<br>";
			foreach ($files as $file) {
				echo "&nbsp; &nbsp; &nbsp; " . $file['file'] . '<br>';
			}
			echo '</div><br><br>';
		}/**/
		return $dataGroups;
	}
	
	protected function getStylesheetResourcesFromData(array $data = array()) {
		$resources = array();
		$cacheDirectory = $this->getMinifyCacheDirectory();
		$cssCompressor = $this->getCssCompressor();

		if ($this->isEnabledForCss() === true) {
			$dataGroups = self::breakResourceDataIntoGroups($data, $this->mergeCss());
			foreach ($dataGroups as $data) {
			    $stylesheetGroups = self::getStylesheetGroups($data);
			    foreach ($stylesheetGroups as $params => $files) {
			        $lastLastModifiedTime = self::getLastModifiedTimeFromFiles($files);
					$fileName = self::getFileName($files, $lastLastModifiedTime, $cssCompressor) . '.css';
					
					if (file_exists($cacheDirectory . DS . 'css' . DS . $fileName . '.var') === false) {
						list($fileName, $fileContents) = self::minifyCss($fileName, $files, $cssCompressor);
						if (file_exists($cacheDirectory . DS . 'css' . DS . $fileName . '.var') === false) {
							self::createCachedFiles('css', $fileName, $fileContents);
						}
					}
		
					$resources[] = array(
	                        'url' => $this->getBaseMinifyUrl('css') . $fileName,
	                        'params' => $params
					);
			    }
			}
		} else {
			foreach ($data as $item) {
				$resources[] = array(
                        'url' => $item['url'],
                        'params' => $item['params']
				);
			}
		}
		
		return $resources;
	}
		
	protected function getScriptResourcesFromData(array $data = array()) {
		$resources = array();
		$cacheDirectory = $this->getMinifyCacheDirectory();
		$jsCompressor = $this->getJsCompressor();
		if ($this->isEnabledForJs() === true) {
			$dataGroups = self::breakResourceDataIntoGroups($data, $this->mergeJs());
			foreach ($dataGroups as $data) {
				$files = array();
				foreach ($data as $item) {
					if (isset($item['type']) and $item['type'] == 'external') {
						$resources[] = array(
							'url' => $item['url'],
							'params' => $item['params'],
						);
					} else $files[] = $this->getFilePathForItem($item, 'script');
				}
				
				if (empty($files)) continue;
				
				$lastLastModifiedTime = self::getLastModifiedTimeFromFiles($files);
	
				$fileName = self::getFileName($files, $lastLastModifiedTime, $jsCompressor) . '.js';
				if (file_exists($cacheDirectory . DS . 'js' . DS . $fileName . '.var') === false) {
					list($fileName, $fileContents) = self::minifyJs($fileName, $files, $jsCompressor);
					if (file_exists($cacheDirectory . DS . 'js' . DS . $fileName . '.var') === false) {
						self::createCachedFiles('js', $fileName, $fileContents);
					}
				}
				
				$resources[] = array(
					'url' => $this->getBaseMinifyUrl('js') . $fileName,
					'params' => $item['params'],
				);
			}
		} else {
			$scriptItems = array();
			foreach ($data as $item) {
				$resources[] = array(
					'url' => $item['url'],
					'params' => $item['params'],
				);
			}
		}
		return $resources;
	}
	
	protected function getRssResourcesFromData(array $data = array()) {
		$resources = array();

		foreach ($data as $item) {
			$resources[] = array(
									'url' => $item['url'],
									'type' => 'application/rss+xml',
									'params' => $item['params']
								);
		}
		return $resources;
	}
	
	protected function getLinkRelFromData(array $data = array()) {
		$resources = array();

		foreach ($data as $item) {
			$resources[] = array(
									'url' => $item['url'],
									'params' => $item['params'] ? ' ' . $item['params'] : ''
								);
		}
		return $resources;
	}
	
	private function getStylesheetGroups(array $stylesheets = array()) {
	    $stylesheetGroups = array();
	    foreach ($stylesheets as $stylesheet) {
	        $stylesheet['params'] = (string) $stylesheet['params'] ? (string) $stylesheet['params'] : 'media="all"';
	        if (array_key_exists($stylesheet['params'], $stylesheetGroups) === false) {
	            $stylesheetGroups[$stylesheet['params']] = array();
	        }
	        
			$stylesheetGroups[$stylesheet['params']][] = $this->getFilePathForItem($stylesheet, 'stylesheet'); 
	    }
	    return $stylesheetGroups;
	}
	
	private function minifyJs($fileName, array $files = array(), $jsCompressor = 'none')
	{
		$this->canSaveCache = false;
		$fileContents = self::getCombinedFilesContent($files, array('js'));
		if (Mage::helper('ewminify')->lock() === false) {
			$fileName = basename($fileName, '.js') . '-raw.js';
			return array($fileName, $fileContents);
		}
		
		$minifiedJs = Extendware_EWMinify_Model_Minify::js($fileContents, array(), $jsCompressor);
		Mage::helper('ewminify')->unlock();
		return array($fileName, $minifiedJs);
	}
	
	private function minifyCss($fileName, array $files = array(), $cssCompressor = 'none')
	{
		$this->canSaveCache = false;
		$fileContents = null;
		$rawFile = $this->getMinifyCacheDirectory() . DS . 'css' . DS . $fileName . '.source';
		if (file_exists($rawFile) === false) {
			foreach ($files as $file) {
			    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
			    if (in_array($ext, array('css'))) {
				    $fileContents .= @Extendware_EWMinify_Model_Minify::cssRewriteUri(self::getFileContents($file), $this->getArea(), dirname($file)) . "\n"; 
			    }
			}
			@file_put_contents($rawFile, $fileContents);
		}
		
		if (!$fileContents) $fileContents = file_get_contents($rawFile);
		if (Mage::helper('ewminify')->lock() === false) {
			$fileName = basename($fileName, '.css') . '-raw.css';
			return array($fileName, $fileContents);
		}

		$fileContents = Extendware_EWMinify_Model_Minify::css($fileContents, array(), $cssCompressor); 
		Mage::helper('ewminify')->unlock();
		return array($fileName, $fileContents);
	}
	
	private function createHtaccessFile() 
	{
	    $cacheDirectory = $this->getMinifyCacheDirectory();
	    $destFile = $cacheDirectory . DS . '.htaccess';
	    if (is_file($destFile) === false or self::$checkFilemtime === true) {
		    $srcFile = Mage::getModuleDir('', 'Extendware_EWMinify') . DS . 'resources' . DS . '.htaccess.template';
			if (@filemtime($srcFile) > @filemtime($destFile)) {
				@copy($srcFile, $destFile);
			}
	    }
	}
	
	private function createCachedFiles($type, $name, $contents) 
	{
	    $cacheDirectory = $this->getMinifyCacheDirectory() . DS . $type;
	    
		if (is_dir($cacheDirectory) === false) {
			Extendware_EWCore_Helper_File::mkdir($cacheDirectory);
		}
		
		self::createHtaccessFile();
		
		$contentType = ($type == 'js' ? 'application/x-javascript' : 'text/css');
		
		$typeMap = '';
		$contentLength = strlen($contents);
		@self::filePutContents($cacheDirectory . DS . $name, $contents, LOCK_EX);
		$typeMap .= self::getTypeMapSection($name, $contentType, null, 0.001);
		
		if (function_exists('gzdeflate')) {
			$compressedContents = gzdeflate($contents, 9);
			@self::filePutContents($cacheDirectory . DS . $name . '.zd', $compressedContents, LOCK_EX);
			$typeMap .= self::getTypeMapSection($name . '.zd', $contentType, 'deflate', $this->getQualityScore($contentLength, strlen($compressedContents)));
		}
		
		if (function_exists('gzencode')) {
			$compressedContents = gzencode($contents, 9);
			@self::filePutContents($cacheDirectory . DS . $name . '.zg', $compressedContents, LOCK_EX);
			$typeMap .= self::getTypeMapSection($name . '.zg', $contentType, 'x-gzip', $this->getQualityScore($contentLength, strlen($compressedContents)));
		}
		
		if (function_exists('gzcompress')) {
			$compressedContents = gzcompress($contents, 9);
			@self::filePutContents($cacheDirectory . DS . $name . '.zc', $compressedContents, LOCK_EX);
			$typeMap .= self::getTypeMapSection($name . '.zc', $contentType, 'x-compress', $this->getQualityScore($contentLength, strlen($compressedContents)));
		}
		
		@self::filePutContents($cacheDirectory . DS . $name . '.var', $typeMap, LOCK_EX);
		
		return $this;
	}
	
	private function filePutContents($file, $contents, $flags = 0) {
		$bool = file_put_contents($file, $contents, $flags);
		if ($bool === false) @unlink($file);
		return $bool;
	}
	
	private function getQualityScore($originalSize, $compressedSize) 
	{
		if ($originalSize == 0) return 1;
		elseif ($compressedSize > $originalSize) return 0;
		elseif ($originalSize == $compressedSize) return 0.001;
		elseif ($originalSize > 0) return sprintf('%.3f', (1 - ($compressedSize / $originalSize)));
		else return 0.001;
	}
	
	private function getTypeMapSection($uri, $contentType, $contentEncoding, $qualityScore)
	{
		$section = 'URI: ' . $uri . "\r\n";
		if ($contentEncoding) $section .= 'Content-Encoding: ' . $contentEncoding . "\r\n";
		$section .= 'Content-Type: ' . $contentType . '; qs=' . $qualityScore . "\r\n\r\n";
		
		return $section;
	}
	
	private function getFileName(array $files = array(), $lastModifiedTime, $compressor) 
	{
		return substr(md5(
					$lastModifiedTime .
					join('&', $files) .
					$compressor .
					$this->getCacheArea() .
					Mage::app()->getStore()->getId() .
					(int)Mage::helper('ewminify')->isDeferJsEnabled() . 
					Mage::getStoreConfig('ewminify/general/java_path') .
					Mage::getStoreConfig('ewminify/general/php_path') .
					Mage::getStoreConfig('ewminify/general/execution_mode') .
					$this->getArea() . 
					(int)Mage::getStoreConfig('ewminify_images/frontend_images/css_image_cache_enabled') .
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .
					(int)Mage::helper('ewminify/config')->isCssImageOptimizingEnabled()
				)
		, 0, 16);
	}
	
	private function getLastModifiedTimeFromFiles(array $files = array()) {
		$lastModifiedTime = 9999999999999;
		if (self::$checkFilemtime === true) {
			$lastModifiedTime = 0;
			foreach ($files as $file) {
				$lastModifiedTime = max($lastModifiedTime, @filemtime($file));
			}
		}
		return $lastModifiedTime;
	}
	
	private function getCombinedFilesContent(array $files = array(), array $allowedExtensions = array('js', 'css'), $separator = "\n") {
	    $contents = '';
		foreach ($files as $file) {
		    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		    if (in_array($ext, $allowedExtensions)) {
		    	$contents .= trim(self::getFileContents($file)) . $separator;
		    }
		}
		
		$contents = Mage::helper('ewminify')->replaceJavascriptEvents($contents);
		
		return trim($contents);
	}
	
	private function getFileContents($file) {
		if (file_exists($file) === false) Mage::helper('ewminify/system')->log('Could not find file: ' . $file, true);
		$content = (string)@file_get_contents($file);
		$content = (pack("CCC",0xef,0xbb,0xbf) === substr($content, 0, 3)) ? substr($content, 3) : $content;
		return $content;
	}
	
	protected function getFilePathForItem(array $item, $type = null) {
		$itemType = @$item['original']['type'];
		if (isset($item['filepath']) and $item['filepath']) return $item['filepath'];
		else if ($itemType == 'js' or $itemType == 'js_css') {
			return BP . DS . Mage::helper('ewminify/config')->getJsCustomPath() . DS . $item['file']; 
		} else {
			return BP . DS . $item['file']; 
		}
	}

	protected function getSkinRelativeFilePath($itemName) {
		$skinUrl = $this->getSkinUrl($itemName);
		$relativeSkinPath = Mage::helper('ewminify')->getPathFragment('skin');
		if (strpos($skinUrl, Mage::getBaseUrl('skin')) !== false) {
			return $relativeSkinPath . ltrim(str_replace(Mage::getBaseUrl('skin'), '', $skinUrl), '/');
		} else {
			$path = parse_url($skinUrl, PHP_URL_PATH);
			if (strpos($relativeSkinPath, $path) === false) {
				$path = $relativeSkinPath . ltrim($path, '/');
			}
			return $path;
		}
	}
}
