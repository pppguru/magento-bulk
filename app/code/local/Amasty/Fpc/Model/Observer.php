<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (http://www.amasty.com)
 * @package Amasty_Fpc
 */

class Amasty_Fpc_Model_Observer
{
    protected $_showBlockNames = false;
    protected $_showBlockTemplates = false;
    protected $_blockHtmlCache = array();

    public function __construct()
    {
        try
        {
            if (
                Mage::app()->useCache('amfpc')
                &&
                Mage::getStoreConfig('amfpc/debug/block_info')
                &&
                Mage::getSingleton('amfpc/fpc_front')->allowedDebugInfo()
                &&
                !Mage::app()->getStore()->isAdmin()
            )
            {
                $this->_showBlockNames = true;
                Mage::app()->getCacheInstance()->banUse('block_html');
            }

            $this->_showBlockTemplates = Mage::getStoreConfig('amfpc/debug/block_templates');
        }
        catch (Mage_Core_Model_Store_Exception $e) // Stores aren't initialized
        {
            $this->_showBlockNames = $this->_showBlockTemplates = false;
        }
    }

    public function actionPredispatch($observer)
    {
        if (Mage::app()->getStore()->isAdmin())
            return;

        if ($page = Mage::registry('amfpc_page'))
        {
            $page = Mage::helper('amfpc')->replaceFormKey($page);

            /** @var Amasty_Fpc_Model_Fpc_Front $front */
            $front = Mage::getSingleton('amfpc/fpc_front');
            $front->debug($page, 'Session Initialized');
            $front->addLoadTimeInfo($page, Amasty_Fpc_Model_Fpc_Front::PAGE_LOAD_HIT_SESSION);

            echo $page;
            exit;
        }

        $request = $observer->getData('controller_action')->getRequest();
        Mage::getSingleton('amfpc/fpc')->validateBlocks($request);
    }

    public function afterToHtml($observer)
    {
        if (!Mage::app()->useCache('amfpc'))
            return;

        if (Mage::app()->getRequest()->isAjax())
            return;

        $block = $observer->getBlock();

        if ($this->_showBlockNames == false)
        {
            if (Mage::getStoreConfig('amfpc/general/dynamic_blocks'))
            {
                /** @var Amasty_Fpc_Model_Config $config */
                $config = Mage::getSingleton('amfpc/config');

                if ($config->blockIsDynamic($block, $isAjax, $tags, $children))
                {
                    $name = $block->getNameInLayout();

                    if (in_array($name, array('global_messages', 'messages')) && $block->getData('amfpc_wrapped'))
                        return;

                    $transport = $observer->getTransport();

                    $html = $transport->getHtml();

                    Mage::getSingleton('amfpc/fpc')->saveBlockCache($name, $html, $tags);

                    $tag = ($isAjax ? 'amfpc_ajax' : 'amfpc');
                    $html = "<$tag name=\"$name\">$html</$tag>";

                    $block->setData('amfpc_wrapped', true);

                    $transport->setHtml($html);
                }

                else if (!empty($children))
                {
                    $transport = $observer->getTransport();

                    $html = $transport->getHtml();

                    foreach ($children as $childName => $tags)
                    {
                        if(preg_match(
                            '#<amfpc\s*name="' . preg_quote($childName) . '"\s*>(.*?)</amfpc>#s',
                            $html,
                            $matches
                        ))
                        {
                            Mage::getSingleton('amfpc/fpc')->saveBlockCache($childName, $matches[1], $tags);
                        }
                    }
                }
            }
        }
        else
        {
            if ($block instanceof Mage_Core_Block_Template)
            {
                $transport = $observer->getTransport();

                $html = $transport->getHtml();

                if ($this->_showBlockTemplates)
                    $templateHint = "<div class=\"amfpc-template-info\">{$block->getTemplateFile()}</div>";
                else
                    $templateHint = '';

                $html = <<<HTML
<div class="amfpc-block-info">
    <div class="amfpc-block-handle"
        onmouseover="$(this).parentNode.addClassName('active')"
        onmouseout="$(this).parentNode.removeClassName('active')"
    >{$block->getNameInLayout()}</div>
    $templateHint
    $html
</div>
HTML;

                $transport->setHtml($html);
            }
        }
    }

    public function layoutRenderBefore()
    {
        $request = Mage::app()->getRequest();

        if ($dynamicBlocks = Mage::registry('amfpc_blocks'))
        {
            $layout = Mage::app()->getLayout();
            $page = $dynamicBlocks['page'];

            Mage::app()->setUseSessionVar(false);

            /** @var Amasty_Fpc_Model_Fpc_Front $front */
            $front = Mage::getSingleton('amfpc/fpc_front');

            foreach ($dynamicBlocks['blocks'] as $name)
            {
                $blockConfig = Mage::app()->getConfig()->getNode('global/amfpc/blocks/'.$name);
                $parent = (string)$blockConfig['parent'];

                $realName = $parent ? $parent : $name;

                if (!isset($this->_blockHtmlCache[$realName]))
                {
                    $block = $layout->getBlock($realName);
                    if ($block)
                    {
                        $this->_blockHtmlCache[$realName] = $block->toHtml();

                        if ($parent)
                        {
                            if (preg_match(
                                '#<amfpc\s*name="' . preg_quote($name) . '"\s*>(.*?)</amfpc>#s',
                                $this->_blockHtmlCache[$realName],
                                $matches
                            ))
                            {
                                $this->_blockHtmlCache[$name] = $matches[1];
                            }
                        }
                    }
                }

                if (isset($this->_blockHtmlCache[$name]))
                {
                    $front->debug($this->_blockHtmlCache[$name], $name . ($parent ? "[$parent]" : '') . ' (refresh)');

                    if (preg_match(
                        '#<amfpc(_ajax)? name="'.preg_quote($name).'" />#',
                        $page,
                        $matches,
                        PREG_OFFSET_CAPTURE))
                    {
                        $page = substr_replace($page, $this->_blockHtmlCache[$name], $matches[0][1], strlen($matches[0][0]));
                    }
                }
            }

            $front->debug($page, 'Late page load');
            $front->addLoadTimeInfo($page, Amasty_Fpc_Model_Fpc_Front::PAGE_LOAD_HIT_UPDATE);

            if (Mage::registry('amfpc_new_session'))
            {
                $page = Mage::helper('amfpc')->replaceFormKey($page);
            }

            $response = Mage::app()->getResponse();
            $response->setBody($page);
            $response->sendHeaders();
            $response->outputBody();

            exit;
        }
        else if ($request->isAjax())
        {
            $blocks = $request->getParam('amfpc_ajax_blocks');
            if ($blocks)
            {
                /** @var Amasty_Fpc_Model_Fpc_Front $front */
                $front = Mage::getSingleton('amfpc/fpc_front');

                Mage::app()->setUseSessionVar(false);

                $blocks = explode(',', $blocks);

                $result = array();
                $layout = Mage::app()->getLayout();
                foreach ($blocks as $name)
                {
                    $block = $layout->getBlock($name);
                    if ($block)
                    {
                        $content = Mage::getSingleton('core/url')->sessionUrlVar($block->toHtml());

                        $front->debug($content, $name . ' (ajax)');
                        $result[$name] = $content;
                    }
                }

                $blocksJson = Mage::helper('core')->jsonEncode($result);

                Mage::app()->getResponse()->setBody($blocksJson)->sendResponse();
                exit;
            }
        }
    }

    protected function _canPreserve()
    {
        if (!Mage::registry('amfpc_preserve'))
            return false;

        if (Mage::app()->getResponse()->getHttpResponseCode() != 200)
            return false;

        if ($this->_showBlockNames)
            return false;

        if ($layout = Mage::app()->getLayout())
        {
            if ($block = $layout->getBlock('messages'))
                if ($block->getMessageCollection()->count() > 0)
                    return false;
            if ($block = $layout->getBlock('global_messages'))
                if ($block->getMessageCollection()->count() > 0)
                    return false;
        }
        else
            return false;

        return true;
    }

    public function setResponse($response, $html, $status)
    {
        /** @var Amasty_Fpc_Model_Fpc_Front $front */
        $front = Mage::getSingleton('amfpc/fpc_front');

        $html = preg_replace(
            '#(<amfpc[^>]*?>|</amfpc>)#s',
            '',
            $html
        );

        $front->addLoadTimeInfo($html, $status);

        $response->setBody($html);
    }

    public function onHttpResponseSendBefore($observer)
    {
        if (Mage::app()->getRequest()->getModuleName() == 'api')
            return;

        if (!Mage::app()->useCache('amfpc'))
            return;

        if (Mage::app()->getStore()->isAdmin())
            return;

        if (Mage::app()->getRequest()->isAjax())
            return;

        // No modifications in response till here

        Mage::getSingleton('core/session')->getFormKey(); // Init form key

        $page = $observer->getResponse()->getBody();

        if ($ignoreStatus = Mage::registry('amfpc_ignored'))
        {
            $this->setResponse($observer->getResponse(), $page, Amasty_Fpc_Model_Fpc_Front::PAGE_LOAD_IGNORE_PARAM);

            return;
        }

        $tags = Mage::getSingleton('amfpc/config')->matchRoute(Mage::app()->getRequest());

        if (!$tags && !Mage::getStoreConfig('amfpc/pages/all'))
        {
            $this->setResponse($observer->getResponse(), $page, Amasty_Fpc_Model_Fpc_Front::PAGE_LOAD_NEVER_CACHE);

            return;
        }

        if (Mage::helper('amfpc')->inIgnoreList() || Mage::registry('amfpc_ignorelist'))
        {
            $this->setResponse($observer->getResponse(), $page, Amasty_Fpc_Model_Fpc_Front::PAGE_LOAD_IGNORE);
            return;
        }


        if ($this->_canPreserve())
        {
            $tags[] = Amasty_Fpc_Model_Fpc::CACHE_TAG;
            $tags[] = Mage_Core_Block_Abstract::CACHE_GROUP;

            $lifetime = +Mage::getStoreConfig('amfpc/general/page_lifetime');
            $lifetime *= 3600;

            Mage::getSingleton('amfpc/fpc')->saveCache($page, $tags, $lifetime);
        }

        $this->setResponse($observer->getResponse(), $page, Amasty_Fpc_Model_Fpc_Front::PAGE_LOAD_MISS);
    }

    public function cleanCache(Mage_Cron_Model_Schedule $schedule)
    {
        Mage::getSingleton('amfpc/fpc')->getFrontend()->clean(Zend_Cache::CLEANING_MODE_OLD);
    }

    public function flushOutOfStockCache($observer)
    {
        $item = $observer->getItem();

        if ($item->getStockStatusChangedAutomatically())
        {
            $tags = array('catalog_product_' . $item->getProductId(), 'catalog_product');
            Mage::dispatchEvent('application_clean_cache', array('tags' => $tags));
        }

        return $item;
    }

    public function onQuoteSubmitSuccess($observer)
    {
        if (Mage::getStoreConfig('amfpc/product/flush_on_purchase'))
        {
            $tags = array();

            $quote = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                $tags []= 'catalog_product_' . $item->getProductId();
                $children   = $item->getChildrenItems();
                if ($children) {
                    foreach ($children as $childItem) {
                        $tags []= 'catalog_product_' . $childItem->getProductId();
                    }
                }
            }

            if (!empty($tags))
                Mage::dispatchEvent('application_clean_cache', array('tags' => $tags));
        }
    }

    public function onApplicationCleanCache($observer)
    {
        $tags = $observer->getTags();

        /**
         * @var Amasty_Fpc_Model_Fpc $fpc
         */
        $fpc = Mage::getSingleton('amfpc/fpc');

        if (!empty($tags))
        {
            if (!is_array($tags))
            {
                $tags = array($tags);
            }

            $productIds = array();
            foreach ($tags as $tag)
            {
                if (preg_match('/^catalog_product_(?P<id>\d+)$/i', $tag, $matches))
                {
                    $productIds[] = +$matches['id'];
                }
            }

            $additionalTags = $fpc->getProductsAdditionalTags($productIds);

            if (!empty($additionalTags))
            {
                $tags = array_merge($tags, $additionalTags);
            }
        }

        $fpc->clean($tags);
    }

    public function onModelSaveBefore($observer)
    {
        $object = $observer->getObject();

        if (class_exists('Mirasvit_AsyncCache_Model_Asynccache', false)
            && $object instanceof Mirasvit_AsyncCache_Model_Asynccache)
        {
            if ($object->getData('status') == Mirasvit_AsyncCache_Model_Asynccache::STATUS_SUCCESS &&
                $object->getOrigData('status') != Mirasvit_AsyncCache_Model_Asynccache::STATUS_SUCCESS)
            {
                Mage::getSingleton('amfpc/fpc')->getFrontend()->clean($object->getMode(), $object->getTagArray(), true);
            }
        }
    }

    public function onCustomerLogin($observer)
    {
        $customer = $observer->getCustomer();

        Mage::getSingleton('customer/session')
            ->setCustomerGroupId($customer->getGroupId());
    }

    public function onReviewSaveAfter($observer)
    {
        $review = $observer->getObject();

        if ($review->getEntityId() == 1) // Product
            Mage::getSingleton('amfpc/fpc')->clean('catalog_product_' . $review->getEntityPkValue());
    }

    public function onQuoteSaveAfter($observer)
    {
        Mage::helper('amfpc')->invalidateBlocksWithAttribute('cart');
    }

    public function onCustomerLoginLogout($observer)
    {
        Mage::helper('amfpc')->invalidateBlocksWithAttribute(array('customer', 'cart'));
    }

    public function onCategorySaveAfter($observer)
    {
        if (Mage::getStoreConfig('amfpc/category/flush_all'))
            Mage::getSingleton('amfpc/fpc')->flush();
    }
}
