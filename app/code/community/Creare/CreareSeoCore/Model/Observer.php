<?php

class Creare_CreareSeoCore_Model_Observer extends Mage_Core_Model_Abstract
{
    protected $helper;
    protected $metaHelper;

    protected function _construct()
    {
        $this->helper = Mage::helper("creareseocore");
        $this->metaHelper = Mage::helper('creareseocore/meta');
        parent::_construct();
    }


    /**
     * @param $observer
     * @return $this
     */
    public function changeRobots($observer)
    {
        $action = $observer->getEvent()->getAction();
        $page = $action->getFullActionName();
        $layout = $observer->getEvent()->getLayout();

        switch ($page)
        {
            case "catalog_category_view" :

                if ($this->helper->getConfig("noindexparams")
                && parse_url($action->getRequest()->getRequestUri(), PHP_URL_QUERY)) {
                    $this->_setRobots($layout);
                }

                break;

            case "catalogsearch_result_index" :
                if ($this->helper->getConfig("noindexparamssearch")) {
                    $this->_setRobots($layout);
                }
                break;

            case "catalog_product_gallery" :
                if ($this->helper->getConfig("noindexparamsgallery")) {
                    $this->setRobots($layout);
                }
                break;
        }

        return $this;
    }

    /**
     * @param $observer
     * @return bool
     */
    public function discontinuedCheck(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getAction()->getRequest();

        if ($request->getControllerModule() !== "Mage_Catalog") {
            return false;
        }

        if ($request->getControllerName() === "product") {

            $product = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('creareseo_discontinued')
                ->addAttributeToSelect('creareseo_discontinued_product')
                ->addAttributeToSelect('name')
                ->addIdFilter($request->getParam('id'))
                ->setPageSize(1)
                ->getFirstItem();

            if ($discontinuedUrl = $this->helper->getDiscontinuedProductUrl($product)) {
                $this->_redirect301($discontinuedUrl, $product->getName());
            }
        }

        if ($request->getControllerName() === "category") {

            $category = Mage::getResourceModel('catalog/category_collection')
                ->addIdFilter($request->getParam('id'))
                ->addAttributeToSelect('name')
                ->setPageSize(1)
                ->getFirstItem();

            if ($discontinuedUrl = $this->helper->getDiscontinuedCategoryUrl($category)) {
                $this->_redirect301($discontinuedUrl, $category->getName());
            }
        }
    }


    /**
     * The function to remove the meta keywords tag and empty
     * description tag
     *
     * @param Varien_Event_Observer $observer
     */
    public function applyTag(Varien_Event_Observer $observer) {
        if ($this->helper->getConfig('metakw')) {
            $body = $observer->getResponse()->getBody();
            if (strpos(strtolower($body), 'meta name="keywords"') !== false) {
                $body = preg_replace('{(<meta name="keywords"[^>]*?>\n)}i', '', $body);
                
            }
            if (strpos(strtolower($body), 'meta name="description" content=""') !== false) {
                $body = preg_replace('{(<meta name="description"[^>]*?>\n)}i', '', $body);
            }
            
            $observer->getResponse()->setBody($body);
        }
    }


    /**
     * Replaces category name with heading on category pages
     *
     * @param $observer
     */
    public function seoHeading(Varien_Event_Observer $observer) {
        
        $category = $observer->getEvent()->getCategory();
        $category->setOriginalName($category->getName());

        if ($this->helper->getConfig("category_h1")
            && $category->getData('creareseo_heading'))
        {
            $category->setName($category->getCreareseoHeading());
        }
    }


    /**
     * On admin_system_config_changed_section_{crearerobots/crearehtaccess}
     * Takes the file, post data and the configuration field and
     * writes the post data to the file.
     *
     * @param Varien_Event_Observer $observer
     */
    public function writeToFileOnConfigSave(Varien_Event_Observer $observer)
    {
        $post = Mage::app()->getRequest()->getPost();
        $robots_location = $post['groups']['files']['fields']['robots_location']['value'];
        $robots_post = $post['groups']['files']['fields']['robots']['value'];
        $htaccess_post = $post['groups']['files']['fields']['htaccess']['value'];

        if ($robots_post) {
            $this->helper->writeFile(
                $this->helper->robotstxt(),
                $robots_post,
                'robots',
                $robots_location
            );
        }

        if ($htaccess_post) {
            $this->helper->writeFile(
                $this->helper->htaccess(),
                $htaccess_post,
                'htaccess'
            );
        }
    }


    /**
     * On controller_action_predispatch takes the file
     * and the configuration field and saves the current file
     * data to the database before the field is loaded
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveConfigOnConfigLoad(Varien_Event_Observer $observer)
    {
        $path = $this->helper->getConfigPath();

        if ($path == 'system_config_crearehtaccess') {
            $this->helper->saveFileContentToConfig($this->helper->htaccess(), 'htaccess');
        }
        if ($path == 'system_config_crearerobots') {
            $this->helper->saveFileContentToConfig($this->helper->robotstxt(), 'robots');
        }
    }


    /**
     * Checks if the page loaded is the canonical
     * version, if not redirects to that version
     *
     * @param Varien_Event_Observer $observer
     */
    public function forceProductCanonical(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('catalog/seo/product_canonical_tag') && !Mage::getStoreConfig('product_use_categories'))
        {
            if ($this->helper->getConfig('forcecanonical')) {
                // check for normal catalog/product/view controller here
                if(!stristr("catalog",Mage::app()->getRequest()->getModuleName()) && Mage::app()->getRequest()->getControllerName() != "product") return;
                // Maintain querystring if one is set (to maintain tracking URLs such as gclid)
                $querystring = ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');
                $product = $observer->getEvent()->getProduct();
                $url = Mage::helper('core/url')->escapeUrl($product->getUrlModel()->getUrl($product, array('_ignore_category'=>true)).$querystring);
                if(Mage::helper('core/url')->getCurrentUrl() != $url){
                    Mage::app()->getFrontController()->getResponse()->setRedirect($url,301);
                    Mage::app()->getResponse()->sendResponse();
                }
            }
        }
    }


    /**
     * Adds the page title and meta description to the contact page's <head>
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function contactsMetaData(Varien_Event_Observer $observer)
    {
        $route = $observer->getEvent()->getAction()->getRequest()->getRouteName();
        $headBlock = $observer->getEvent()->getLayout()->getBlock('head');

        if ($route !== "contacts" || !is_object($headBlock)) {
            return false;
        }

        if ($title = $this->metaHelper->config('contacts_title')) {
            $headBlock->setTitle($title);
        }

        if ($metaDesc = $this->metaHelper->config('contacts_metadesc')) {
            $headBlock->setDescription($metaDesc);
        }

    }


    /**
     * If set, replaces the homepage title with the definitive one set in the config
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function forceHomepageTitle(Varien_Event_Observer $observer)
    {
        $actionName = $observer->getEvent()->getAction()->getFullActionName();

        if ($actionName !== "cms_index_index"
        || !$this->helper->getConfig('forcehptitle')) {
            return false;
        }

        $layout = $observer->getEvent()->getLayout();
        $homepage = Mage::getStoreConfig('web/default/cms_home_page');
        $title = Mage::getModel('cms/page')->load($homepage, 'identifier')->getTitle();

        if ($title) {
            if ($head = $layout->getBlock('head')) {
                $head->setData('title', $title);
            }
        }

    }


    /**
     * On relevant pages, will override the page title with
     * the fallback if one isn't set in the editor
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function setFinalPageTitle(Varien_Event_Observer $observer)
    {
        $actionName = $observer->getEvent()->getAction()->getFullActionName();

        if ($actionName === "cms_index_index"
        || $actionName === "contacts_index_index") {
            return false;
        }

        $layout = $observer->getEvent()->getLayout();
        $title = $this->_getFinalPageTitle();

        if ($title !== false) {
            if ($head = $layout->getBlock('head')) {
                $head->setTitle($title);
            }
        }

        return false;
    }

    /**
     * On relevant pages, will override the meta desc with
     * the fallback if one isn't set in the editor
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function setFinalMetaDescription(Varien_Event_Observer $observer)
    {
        $actionName = $observer->getEvent()->getAction()->getFullActionName();
        $headBlock = $observer->getEvent()->getLayout()->getBlock('head');

        if ($actionName === "contacts_index_index") {
            return false;
        }

        if ($description = $this->_getFinalMetaDescription()) {
            $headBlock->setDescription($description);
        }

        return false;
    }


    /**
     * @param $observer
     */
    public function setMandatoryAltTag(Varien_Event_Observer $observer)
    {
        if ($this->helper->getConfig('mandatory_alt')) {
            $observer->getBlock()->setTemplate('creareseo/catalog/product/helper/gallery.phtml');
        }
    }


    /**
     * Sets Google Analytics to use UA when the version is less
     * than 1.9.1 and it is set in the config
     *
     * @param $observer
     */
    public function setUA(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('creareseocore/googleanalytics/type')
            && version_compare(Mage::getVersion(), '1.9.1', '<'))
        {
            $layout = $observer->getEvent()->getLayout();
            $layout->getUpdate()->addUpdate('<reference name="after_body_start"><remove name="google_analytics" /><block type="creareseocore/googleanalytics_ua" name="universal_analytics" template="creareseo/google/ua.phtml" /></reference>');
            $layout->generateXml();
        }
    }


    private function _setRobots($layout)
    {
        return $layout->getUpdate()->addUpdate('<reference name="head"><action method="setRobots"><value>NOINDEX,FOLLOW</value></action></reference>');
    }


    private function _redirect301($url, $name)
    {
        Mage::getSingleton('core/session')
            ->addNotice($this->helper->__('%s has been discontinued', $name));
        Mage::app()->getFrontController()->getResponse()->setRedirect($url, 301);
        Mage::app()->getResponse()->sendResponse();
        exit;
    }


    private function _getFallbackFromConfig($pageType, $tag)
    {
        $isEnabled = $this->metaHelper->config($pageType.'_'.$tag.'_enabled');
        $title = $this->metaHelper->config($pageType.'_'.$tag);

        return $isEnabled && $title
            ? $this->metaHelper->shortcode($title)
            : false;
    }


    private function _getFinalPageTitle()
    {
        $pageType = $this->metaHelper->getPageType();

        if ($pageType === false) {
            $this->_data['title'] = Mage::getStoreConfig('design/head/default_title');
        }

        switch ($pageType) {
            case "product" || "category" :
                $this->_data['title'] = (!$pageType->model->getMetaTitle())
                    ? $this->_getFallbackFromConfig($pageType->code, 'title')
                    : $pageType->model->getMetaTitle();

                if (empty($this->_data['title'])) {
                    $this->_data['title'] = $pageType->model->getName();
                }

                break;
            case "cms" :
                $this->_data['title'] = !empty($pageType->model->getTitle())
                    ? $pageType->model->getTitle()
                    : $this->getDefaultTitle();
                break;
        }

        return $this->metaHelper->cleanString($this->_data['title']);
    }


    private function _getFinalMetaDescription()
    {
        $pageType = $this->metaHelper->getPageType();

        if ($pageType === false) {
            return false;
        }

        $this->_data['description'] = $pageType->model->getMetaDescription()
            ? $pageType->model->getMetaDescription()
            : $this->_getFallbackFromConfig($pageType->code, 'metadesc');

        if (empty($this->_data['description'])) {
            $this->_data['description'] = "";
        }

        return $this->_data['description'];
    }

}
