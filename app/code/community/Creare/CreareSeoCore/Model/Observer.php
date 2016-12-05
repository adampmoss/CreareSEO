<?php

class Creare_CreareSeoCore_Model_Observer extends Mage_Core_Model_Abstract
{
    protected $helper;

    public function _construct()
    {
        $this->helper = Mage::helper("creareseocore");
        parent::_construct();
    }


    private function setRobots($layout)
    {
        return $layout->getUpdate()->addUpdate('<reference name="head"><action method="setRobots"><value>NOINDEX,FOLLOW</value></action></reference>');
    }


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
                    $this->setRobots($layout);
                }

                break;

            case "catalogsearch_result_index" :
                if ($this->helper->getConfig("noindexparamssearch")) {
                    $this->setRobots($layout);
                }
                break;

            case "catalog_product_gallery" :
                if ($this->helper->getConfig("noindexparamsgallery")) {
                    $this->setRobots($layout);
                }
                break;

            case "checkout_cart_index" :
                if ($this->helper->getConfig("noindexparamscart")) {
                    $this->setRobots($layout);
                }
                break;

            case "customer_account_login" :
                if ($this->helper->getConfig("noindexparamsaccount")) {
                    $this->setRobots($layout);
                }
                break;
        }

        return $this;
    }

    private function redirect301($url, $name)
    {
        Mage::getSingleton('core/session')
            ->addNotice($this->helper->__('%s has been discontinued', $name));
        Mage::app()->getFrontController()->getResponse()->setRedirect($url, 301);
        Mage::app()->getResponse()->sendResponse();
        exit;
    }

    public function discontinuedCheck($observer)
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
                $this->redirect301($discontinuedUrl, $product->getName());
            }
        }

        if ($request->getControllerName() === "category") {

            $category = Mage::getResourceModel('catalog/category_collection')
                ->addIdFilter($request->getParam('id'))
                ->addAttributeToSelect('name')
                ->setPageSize(1)
                ->getFirstItem();

            if ($discontinuedUrl = $this->helper->getDiscontinuedCategoryUrl($category)) {
                $this->redirect301($discontinuedUrl, $category->getName());
            }
        }
    }

    /* The function to remove the meta keywords tag */

    public function applyTag($observer) {
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

    /* Replaces category name with heading on category pages */

    public function seoHeading($observer) {
        
        $category = $observer->getEvent()->getCategory();
        $category->setOriginalName($category->getName());

        if ($this->helper->getConfig("category_h1")
            && $category->getData('creareseo_heading'))
        {
            $category->setName($category->getCreareseoHeading());
        }
    }

    /*
     * On admin_system_config_changed_section_{crearerobots/crearehtaccess}
     * Takes the file, post data and the configuration field and 
     * writes the post data to the file.
     */

    public function writeToFileOnConfigSave(Varien_Event_Observer $observer)
    {
        $post = Mage::app()->getRequest()->getPost();
        $robots_location = $post['groups']['files']['fields']['robots_location']['value'];
        $robots_post = $post['groups']['files']['fields']['robots']['value'];
        $htaccess_post = $post['groups']['files']['fields']['htaccess']['value'];

        if ($robots_post) {
            $this->helper->writeFile($this->helper->robotstxt(), $robots_post, 'robots', $robots_location);
        }

        if ($htaccess_post) {
            $this->helper->writeFile($this->helper->htaccess(), $htaccess_post, 'htaccess');
        }
    }

    /*
     * On controller_action_predispatch
     * Takes the file and the configuration field and saves the
     * current file data to the database before the field is loaded
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


    /* Checks if the page loaded is the canonical version, if not redirects to that version */
    
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

    /* Adds the page title and meta description to the contact page's <head> */
    
    public function contactsMetaData(Varien_Event_Observer $observer)
    {
        $route = $observer->getEvent()->getAction()->getRequest()->getRouteName();
        $headBlock = $observer->getEvent()->getLayout()->getBlock('head');

        if ($route !== "contacts" || !is_object($headBlock)) {
            return false;
        }

        if ($title = $this->metaHelper()->config('contacts_title')) {
            $headBlock->setTitle($title);
        }

        if ($metaDesc = $this->metaHelper()->config('contacts_metadesc')) {
            $headBlock->setDescription($metaDesc);
        }

    }

    /* If set, replaces the homepage title with the definitive one set in the config */

    public function forceHomepageTitle($observer)
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

    /* On relevant pages, will override the page title with the fallback if one isn't set in the editor */
    
    public function setTitle($observer)
    {
        $actionName = $observer->getEvent()->getAction()->getFullActionName();

        if ($actionName === "cms_index_index"
        || $actionName === "contacts_index_index") {
            return false;
        }

        $layout = $observer->getEvent()->getLayout();
        $title = $this->getTitle();

        if ($title) {
            if ($head = $layout->getBlock('head')) {
                $head->setTitle($title);
            }
        }
    }

    /* On relevant pages, will override the meta desc with the fallback if one isn't set in the editor */
    
    public function setDescription($observer)
    {
        $actionName = $observer->getEvent()->getAction()->getFullActionName();

        if ($actionName === "contacts_index_index") {
            return false;
        }

        $layout = $observer->getEvent()->getLayout();
        $description = $this->getDescription();

        if($description) {
            if ($head = $layout->getBlock('head')) {
                $head->setDescription($description);
            }
        }
    }
	
	public function getDefaultTitle()
    {
        return Mage::getStoreConfig('design/head/default_title');
    }
    
    public function getTitle()
    {
        $pagetype = $this->metaHelper()->getPageType();
        
        if ($pagetype !== false) {
        
            if ($pagetype->_code != "cms") {
                if (!$pagetype->_model->getMetaTitle()) {
                    $this->_data['title'] = $this->setConfigTitle($pagetype->_code);
                } else {
                    $this->_data['title'] = $pagetype->_model->getMetaTitle();
                }
            } else if($pagetype !== false && $pagetype->_code == "cms"){
                $this->_data['title'] = $pagetype->_model->getTitle();
            }

            if (empty($this->_data['title'])) {

                // check if it's a category or product and default to name.
                if($pagetype->_code == "category" || $pagetype->_code == "product"){
                    $this->_data['title'] = $pagetype->_model->getName();
                } else {
                    $this->_data['title'] = $this->getDefaultTitle();
                }
            }
        } else {
            $this->_data['title'] = $this->getDefaultTitle();
        }
        
        return htmlspecialchars(html_entity_decode(trim($this->_data['title']), ENT_QUOTES, 'UTF-8'));
    }
    
    public function setConfigTitle($pagetype)
    {
        if ($this->metaHelper()->config($pagetype.'_title_enabled')) {
            return $this->metaHelper()->getDefaultTitle($pagetype);
        }
    }
    
    public function setConfigMetaDescription($pagetype)
    {
        if ($this->metaHelper()->config($pagetype.'_metadesc_enabled')) {
            return $this->metaHelper()->getDefaultMetaDescription($pagetype);
        }
    }
    
    
    public function getDescription()
    {
        $pagetype = $this->metaHelper()->getPageType();
        
        if ($pagetype !== false) {
            if (!$pagetype->_model->getMetaDescription()) {
                $this->_data['description'] = $this->setConfigMetaDescription($pagetype->_code);
            } else {
                $this->_data['description'] = $pagetype->_model->getMetaDescription();
            }
        }
        
        if (empty($this->_data['description'])) {
            $this->_data['description'] = "";
        }
        return $this->_data['description'];
    }
    
    public function metaHelper()
    {
        return Mage::helper('creareseocore/meta');
    }
    
    public function setMandatoryAltTag($observer)
    {
        if ($this->helper->getConfig('mandatory_alt')) {
            $observer->getBlock()->setTemplate('creareseo/catalog/product/helper/gallery.phtml');
        }
    }

    /* Sets Google Analytics to use UA when the version is less that 1.9.1 and it is set in the config */

    public function setUA($observer)
    {
        $magentoVersion = Mage::getVersion();

        if (Mage::getStoreConfig('creareseocore/googleanalytics/type') && version_compare($magentoVersion, '1.9.1', '<'))
        {
            $layout = $observer->getEvent()->getLayout();
            $layout->getUpdate()->addUpdate('<reference name="after_body_start"><remove name="google_analytics" /><block type="creareseocore/googleanalytics_ua" name="universal_analytics" template="creareseo/google/ua.phtml" /></reference>');
            $layout->generateXml();
        }
    }

}
