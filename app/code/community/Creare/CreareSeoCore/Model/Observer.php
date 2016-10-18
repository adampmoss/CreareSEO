<?php

class Creare_CreareSeoCore_Model_Observer extends Mage_Core_Model_Abstract {

    /* Our function to change the META robots tag on Parameter based category pages */

    public function changeRobots($observer) {

        if (Mage::getStoreConfig('creareseocore/defaultseo/noindexparams')) {
            if ($observer->getEvent()->getAction()->getFullActionName() === 'catalog_category_view') {
                $uri = $observer->getEvent()->getAction()->getRequest()->getRequestUri();
                if (stristr($uri, "?")):
                    $layout = $observer->getEvent()->getLayout();
                    $product_info = $layout->getBlock('head');
                    $layout->getUpdate()->addUpdate('<reference name="head"><action method="setRobots"><value>NOINDEX,FOLLOW</value></action></reference>');
                    $layout->generateXml();
                endif;
            }
        }
        if (Mage::getStoreConfig('creareseocore/defaultseo/noindexparamssearch')) {
            if ($observer->getEvent()->getAction()->getFullActionName() === 'catalogsearch_result_index') {
                $layout = $observer->getEvent()->getLayout();
                $product_info = $layout->getBlock('head');
                $layout->getUpdate()->addUpdate('<reference name="head"><action method="setRobots"><value>NOINDEX,FOLLOW</value></action></reference>');
                $layout->generateXml();
            }
        }
        if (Mage::getStoreConfig('creareseocore/defaultseo/noindexparamsgallery')) {
            if ($observer->getEvent()->getAction()->getFullActionName() === 'catalog_product_gallery') {
                $layout = $observer->getEvent()->getLayout();
                $product_info = $layout->getBlock('head');
                $layout->getUpdate()->addUpdate('<reference name="head"><action method="setRobots"><value>NOINDEX,FOLLOW</value></action></reference>');
                $layout->generateXml();
            }
        }
        return $this;
    }

    public function discontinuedCheck($observer) {
        $data = $observer->getEvent()->getAction()->getRequest();
        if ($data->getControllerModule() === "Mage_Catalog") {
            $id = $data->getParam('id');
            if ($data->getControllerName() === "product") {
                $product = Mage::getModel('catalog/product')->load($id);
                $url = Mage::helper('creareseocore')->getDiscontinuedProductUrl($product);
                if ($url) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('creareseocore')->__('Unfortunately the product %s has been discontinued', $product->getName()));
                    Mage::app()->getFrontController()->getResponse()->setRedirect($url, 301);
                    Mage::app()->getResponse()->sendResponse();
                    exit;
                }
            }
            if ($data->getControllerName() === "category") {
                $id = $data->getParam('id');
                $category = Mage::getModel('catalog/category')->load($id);
                $url = Mage::helper('creareseocore')->getDiscontinuedCategoryUrl($category);
                if ($url) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('creareseocore')->__('Unfortunately the category %s" has been discontinued', $category->getName()));
                    Mage::app()->getFrontController()->getResponse()->setRedirect($url, 301);
                    Mage::app()->getResponse()->sendResponse();
                    exit;
                }
            }
        }
    }

    /* The function to remove the meta keywords tag */

    public function applyTag($observer) {
        if (Mage::getStoreConfig('creareseocore/defaultseo/metakw')) {
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

        if (Mage::getStoreConfig('creareseocore/defaultseo/category_h1'))
        {
            if ($category->getData('creareseo_heading'))
            {
                $category->setName($category->getCreareseoHeading());
            }
        }
    }

    /*
     * On admin_system_config_changed_section_{crearerobots/crearehtaccess}
     * Takes the file, post data and the configuration field and 
     * writes the post data to the file.
     */

    public function writeToFileOnConfigSave($observer) {

        $helper = Mage::helper('creareseocore');
        $post = Mage::app()->getRequest()->getPost();
        $robots_location = $post['groups']['files']['fields']['robots_location']['value'];
        $robots_post = $post['groups']['files']['fields']['robots']['value'];
        $htaccess_post = $post['groups']['files']['fields']['htaccess']['value'];

        if ($robots_post) {
            $helper->writeFile($helper->robotstxt(), $robots_post, 'robots', $robots_location);
        }

        if ($htaccess_post) {
            $helper->writeFile($helper->htaccess(), $htaccess_post, 'htaccess');
        }
    }

    /*
     * On controller_action_predispatch
     * Takes the file and the configuration field and saves the
     * current file data to the database before the field is loaded
     */

    public function saveConfigOnConfigLoad($observer) {
        $helper = Mage::helper('creareseocore');
        $path = $helper->getConfigPath();
        if ($path == 'system_config_crearehtaccess') {
            $helper->saveFileContentToConfig($helper->htaccess(), 'htaccess');
        }
        if ($path == 'system_config_crearerobots') {
            $helper->saveFileContentToConfig($helper->robotstxt(), 'robots');
        }
    }

    /*
        Below script is depreciated in 1.2 as it causes need to reindex on every product save.
        This observer script is no longer called on event controller_action_predispatch
    */

    /*public function productCheck(Varien_Event_Observer $observer) {
        if(Mage::app()->getRequest()->getControllerName() == "catalog_product" && Mage::app()->getRequest()->getActionName() == "validate"){
            $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','name');
            if ($attributeId) {
                $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                if(Mage::getStoreConfig('creareseocore/validate/name')){
                    $attribute->setIsUnique(1)->save();
                } else {
                    $attribute->setIsUnique(0)->save();
                }
            }
            $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','description');
            if ($attributeId) {
                $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                if(Mage::getStoreConfig('creareseocore/validate/description')){
                    $attribute->setIsUnique(1)->save();
                } else {
                    $attribute->setIsUnique(0)->save();
                }
            }
            $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','short_description');
            if ($attributeId) {
                $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                if(Mage::getStoreConfig('creareseocore/validate/short_description')){
                    $attribute->setIsUnique(1)->save();
                } else {
                    $attribute->setIsUnique(0)->save();
                }
            }
        }
    }*/

    /* Checks if the page loaded is the canonical version, if not redirects to that version */
    
    public function forceProductCanonical(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('catalog/seo/product_canonical_tag') && !Mage::getStoreConfig('product_use_categories'))
        {
            if (Mage::getStoreConfig('creareseocore/defaultseo/forcecanonical')) {
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
        if ($observer->getEvent()->getAction()->getRequest()->getRouteName() === "contacts") {
            if (Mage::helper('creareseocore/meta')->config('contacts_title')) {
                $observer->getEvent()->getLayout()->getBlock('head')->setTitle(Mage::helper('creareseocore/meta')->config('contacts_title'));
            }

            if (Mage::helper('creareseocore/meta')->config('contacts_metadesc')) {
                $observer->getEvent()->getLayout()->getBlock('head')->setDescription(Mage::helper('creareseocore/meta')->config('contacts_metadesc'));
            }
        }
    }

    /* If set, replaces the homepage title with the definitive one set in the config */

    public function forceHomepageTitle($observer)
    {
        if (Mage::getStoreConfig('creareseocore/defaultseo/forcehptitle')) {
            if($observer->getEvent()->getAction()->getFullActionName() === "cms_index_index"){
                $layout = $observer->getEvent()->getLayout();
                $homepage = Mage::getStoreConfig('web/default/cms_home_page');
                $title = Mage::getModel('cms/page')->load($homepage, 'identifier')->getTitle();
                if($title){
                    if ($head = $layout->getBlock('head'))
                    {
                        $head->setData('title',$title);
                    }
                }
            }
        }
    }

    /* On relevant pages, will override the page title with the fallback if one isn't set in the editor */
    
    public function setTitle($observer)
    {
        if (Mage::getStoreConfig('creareseocore/defaultseo/forcehptitle') && $observer->getEvent()->getAction()->getFullActionName() == "cms_index_index") return;
	if ($observer->getEvent()->getAction()->getFullActionName() === "contacts_index_index") return;
            $layout = $observer->getEvent()->getLayout();
            $title = $this->getTitle();
            if($title)
            {
                if ($head = $layout->getBlock('head'))
                {
                    $head->setTitle($title);
                }
            }
            
            $layout->generateXml();
    }

    /* On relevant pages, will override the meta desc with the fallback if one isn't set in the editor */
    
    public function setDescription($observer)
    {
	if ($observer->getEvent()->getAction()->getFullActionName() === "contacts_index_index") return;
        $layout = $observer->getEvent()->getLayout();
        $description = $this->getDescription();
        if($description)
        {
            if ($head = $layout->getBlock('head'))
            {
                $head->setDescription($description);
            }
        }
            
        $layout->generateXml();
    }
	
	public function getDefaultTitle()
    {
        return Mage::getStoreConfig('design/head/default_title');
    }
    
    public function getTitle()
    {
        $pagetype = $this->metaHelper()->getPageType();
        
        if ($pagetype !== false)
        {
        
            if ($pagetype->_code != "cms")
            {
                if (!$pagetype->_model->getMetaTitle())
                {
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
        if ($this->metaHelper()->config($pagetype.'_title_enabled'))
        {
            return $this->metaHelper()->getDefaultTitle($pagetype);
        }
    }
    
    public function setConfigMetaDescription($pagetype)
    {
        if ($this->metaHelper()->config($pagetype.'_metadesc_enabled'))
        {
            return $this->metaHelper()->getDefaultMetaDescription($pagetype);
        }
    }
    
    
    public function getDescription()
    {
        $pagetype = $this->metaHelper()->getPageType();
        
        if ($pagetype !== false)
        {
            if (!$pagetype->_model->getMetaDescription())
            {
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
        if (Mage::getStoreConfig('creareseocore/defaultseo/mandatory_alt'))
        {
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
