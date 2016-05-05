<?php

/*
* @category    Module
* @package     MageStack_Varnish
*/

class MageStack_Varnish_Model_Observer {

    /**
     * Identify if page needs to be cached with Varnish
     *
     * @param $observer Varien_Event_Observer
     */
    public function varnish(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $helper = Mage::helper('varnish/cache');

        // Cache disabled in Admin / System / Cache Management
        if (!Mage::helper('varnish')->useVarnishCache()) {
            $helper->turnOffVarnish();
            return false;
        }

        if ($helper->matchException()) {
            $helper->skipVarnish();
            return false;
        }

        if ($helper->isNoCacheStable()){
            return false;
        }

        if ($helper->pollVerification()) {
            $helper->setNoCacheStable();
            return false;
        }

        if ($helper->quoteHasItems() || $helper->isCustomerLoggedIn() || $helper->hasCompareItems()) {
            $helper->turnOffVarnish();
            return false;
        }

        $helper->turnOnVarnish();
    }

    /**
     * @see Mage_Core_Model_Cache
     *
     * @param Mage_Core_Model_Observer $observer
     */
    public function onCategorySave($observer)
    {
        $category = $observer->getCategory();

        if ($category->getData('include_in_menu')) {
            // Notify user that varnish needs to be refreshed
            Mage::app()->getCacheInstance()->invalidateType(array('varnish'));
        }

        return $this;
    }

    /**
     * Purge the relevant URLs when a product, a category or a CMS page is modified
     *
     * @param $observer Mage_Core_Model_Observer
     */
    public function purgeCache($observer)
    {
        // If Varnish isn't turned on, exit
        if (!Mage::helper('varnish')->useVarnishCache()) {
            return;
        }

        $tags = $observer->getTags();
        $urls = array();

        if ($tags == array()) {
            $errors = Mage::helper('varnish')->purgeEverything();
            if (!empty($errors)) {
                Mage::getSingleton('adminhtml/session')->addError('The Varnish Purge has failed');
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess('The Varnish cache storage has been flushed successfully');
            }

            return;
        }

        // compute the urls for affected entities
        foreach ((array)$tags as $tag) {

            $tag_fields = explode('_', $tag); //catalog_product_100 or catalog_category_186

            if (count($tag_fields) == 3) {

                if ($tag_fields[1] == 'product') {

                    // get urls for product
                    try {
                      $product = Mage::getModel('catalog/product')->load($tag_fields[2]);
                      $urls = array_merge($urls, $this->_getUrlsForProduct($product));

                        if ($product->getTypeId() == 'simple') {

                            $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
                            if (!$parentIds)
                                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

                            if (isset($parentIds[0])) {
                                $parent = Mage::getModel('catalog/product')->load($parentIds[0]);
                                $urls = array_merge($urls, $this->_getUrlsForProduct($parent));
                            }
                        }

                        $urls = array_unique($urls);

                    } catch(Exception $e){
                        //Mage::getSingleton('adminhtml/session')->addError($e);
                    }

                } elseif ($tag_fields[1] == 'category') {

                    $category = Mage::getModel('catalog/category')->load($tag_fields[2]);

                    try {
                        $category_urls = $this->_getUrlsForCategory($category);
                        $urls = array_merge($urls, $category_urls);
                    } catch(Exception $e){
                        //Mage::getSingleton('adminhtml/session')->addError($e);
                    }
                } elseif ($tag_fields[1]=='page') {
                    $urls = $this->_getUrlsForCmsPage($tag_fields[2]);
                }
            }
        }

        // Transform URLs to relative URLs
        $relativeUrls = array();

        foreach ($urls as $url) {
            $relativeUrls[] = parse_url($url, PHP_URL_PATH);
        }

        if (!empty($relativeUrls)) {

            $errors = Mage::helper('varnish')->purge($relativeUrls);
            if (!empty($errors)) {
                Mage::getSingleton('adminhtml/session')->addError('Some Varnish purges have failed: <br/>' . implode('<br/>', $errors));
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess('All Purges have been submitted successfully: <br/>' . implode('<br/>', $relativeUrls));
            }
        }

        return $this;
    }

    /**
     * Returns all the URLs related to a product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _getUrlsForProduct($product)
    {
        $urls = array();

        $storeId = $product->getStoreId();
        $routePath = 'catalog/product/view';

        $routeParams['id']  = $product->getId();
        $routeParams['s']   = $product->getUrlKey();

        $routeParams['_store'] = ($storeId === false) ? 1 : $storeId;

        // Collect all rewrites
        $rewrites = Mage::getModel('core/url_rewrite')->getCollection();

        if (!Mage::getConfig('catalog/seo/product_use_categories')) {
            $rewrites->getSelect()->where("id_path = 'product/{$product->getId()}'");
        } else {
            // Also show full links with categories
            $rewrites->getSelect()->where("id_path = 'product/{$product->getId()}' OR id_path like 'product/{$product->getId()}/%'");
        }

        foreach ($rewrites as $r) {

            unset($routeParams);

            $routePath = '';
            $routeParams['_store'] = $r->getStoreId();

            $routeParams['_direct'] = $r->getRequestPath();

            $url = Mage::getUrl($routePath, $routeParams);
            $urls[] = $url;

            $routeParams['_direct'] = $r->getTargetPath();
            $url = Mage::getUrl($routePath, $routeParams);

            $urls[] = $url;
        }

        return $urls;
    }


    /**
     * Returns all the URLs related to a category
     *
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _getUrlsForCategory($category)
    {
        $urls = array();
        $routePath = 'catalog/category/view';

        $storeId = $category->getStoreId();

        $routeParams['id']  = $category->getId();
        $routeParams['s']   = $category->getUrlKey();

         $routeParams['_store'] = ($storeId === false) ? 1 : $storeId;

        $url = Mage::getUrl($routePath, $routeParams);
        $urls[] = $url;

        // Collect all rewrites
        $rewrites = Mage::getModel('core/url_rewrite')->getCollection();

        $rewrites->getSelect()->where("id_path = 'category/{$category->getId()}'");

        foreach($rewrites as $r) {

            unset($routeParams);

            $routePath = '';
            $routeParams['_direct'] = $r->getRequestPath();
            $routeParams['_store'] = $r->getStoreId();
            $routeParams['_nosid'] = true;

            $url = Mage::getUrl($routePath, $routeParams);
            $urls[] = $url;
        }

        return $urls;
    }

    /**
     * Returns all the URLs related to a CMS Page
     *
     * @param int $cmsPageId
     */
    protected function _getUrlsForCmsPage($cmsPageId)
    {
        $urls = array();
        $page = Mage::getModel('cms/page')->load($cmsPageId);
        if ($page->getId()) {
            $urls[] = '/' . $page->getIdentifier();
        }

        return $urls;
    }

    /**
     * Purges all URLs in a batch process via cron task
     */
    public function cronBatchPurge($observer)
    {
        Mage::helper('varnish')->purgeProcess();
    }

}

