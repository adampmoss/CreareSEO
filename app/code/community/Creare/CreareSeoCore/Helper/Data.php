<?php
class Creare_CreareSeoCore_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_CONFIG_PATH = 'creareseocore/defaultseo/';

    public function getConfig($field)
    {
        return Mage::getStoreConfig(self::DEFAULT_CONFIG_PATH.$field);
    }

    public function getDiscontinuedProductUrl($product)
    {
        $type = $product->getAttributeText('creareseo_discontinued');

        // check to see if we want to redirect to a product / category / homepage
        if($type === '301 Redirect to Category'){
            $cats = $product->getCategoryIds();
            if (is_array($cats) && count($cats) > 1) {
                $cat = Mage::getModel('catalog/category')->load( $cats[0] ); 
                return $cat->getUrlPath();
            } else {
                $cat = Mage::getModel('catalog/category')->load( $cats ); 
                return $cat->getUrlPath();
            }
        }

        if($type === '301 Redirect to Homepage'){
            return Mage::getBaseUrl();
        }   

        if($type === '301 Redirect to Product SKU'){

            $sku = $product->getCreareseoDiscontinuedProduct();
            if($sku){
                $productUrl = Mage::getModel('catalog/product')->getCollection()
                     ->addAttributeToSelect('sku')
                     ->addFieldToFilter('sku',$sku)
                      ->getFirstItem()
                      ->getProductUrl();

                if ($productUrl)
                {
                    return $productUrl;
                }

            }
        }

        return false;

    }

    public function getDiscontinuedCategoryUrl($category){
        if($category->getLevel() == 2){
            if(!$category->getIsActive()){
                return Mage::getBaseUrl();
            } else {
                return Mage::getBaseUrl().$category->getUrlPath();
            }
        } else {
            $parentCategory = Mage::getModel('catalog/category')->load($category->getParentId());
            return $this->getDiscontinuedCategoryUrl($parentCategory);
        }

    }

    public function getConfigPath()
    {
        return Mage::app()->getRequest()->getControllerName().'_'.Mage::app()->getRequest()->getParam('section');
    }
    
    
    /* 
     * On controller_action_predispatch called by saveConfigOnConfigLoad()
     */
    
    public function saveFileContentToConfig($file, $field)
    {
        $adminsession = Mage::getSingleton('adminhtml/session');
        $io = new Varien_Io_File();
        $io->open(array('path' => Mage::getBaseDir()));
        
        if ($io->fileExists($file))
        {
            try
            {
                $contents = $io->read($file);
                Mage::getModel('core/config')->saveConfig('creare'.$field.'/files/'.$field, $contents);
                
            } catch(Mage_Core_Exception $e)
            {
                $adminsession->addError($e->getMessage());
            }
        } else {
            $adminsession->addError($file." does not exist. Please create this file on your domain root to use this feature.");
        }
            
        $io->streamClose();
    }
    
    /* 
     * On admin_system_config_changed_section_ called by writeToFileOnConfigSave()
     */
    
    public function writeFile($file, $post, $field, $robots_location = '')
    {
        $adminsession = Mage::getSingleton('adminhtml/session');
        $io = new Varien_Io_File();
        $io->open(array('path' => Mage::getBaseDir().DS.$robots_location));
        
        if ($io->fileExists($file))
        {
            if ($io->isWriteable($file))
            {
                try
                {
                    $io->streamOpen($file);
                    $io->streamWrite($post);

                } catch(Mage_Core_Exception $e)
                {
                    $adminsession->addError($e->getMessage());
                }
            } else {
            
                $adminsession->addError($file." is not writable. Change permissions to 644 to use this feature.");
            
            }
        } else {
            
            $adminsession->addError($file." does not exist. The file was not saved.");
        }
            
        $io->streamClose();
    }
    
    public function isWriteable($file)
    {
        $io = new Varien_Io_File();
        $io->open(array('path' => Mage::getBaseDir()));
        return $io->isWriteable($file);
    }
    
    public function exists($file)
    {
        $io = new Varien_Io_File();
        $io->open(array('path' => Mage::getBaseDir()));
        return $io->fileExists($file);
    }
    
    public function robotstxt()
    {
        return 'robots.txt';
    }
    
    public function htaccess()
    {
        return '.htaccess';
    }
    
    public function checkDefaultStoreNames()
    {
        $stores = Mage::getModel('core/store')->getCollection();
        foreach($stores as $store){
            if($store->getName() == "Default Store View"){
                return false;
            }
        }
        return true;
    }
    
    public function checkDefaultStoreGroupNames()
    {
        $storegroups = Mage::getModel('core/store_group')->getCollection();
        foreach($storegroups as $storegroup){
            if($storegroup->getName() == "Main Store"){
                return false;
            }
        }
        return true;
    }
    
    public function checkDefaultWebsiteNames()
    {
        $websites = Mage::getModel('core/website')->getCollection();
        foreach($websites as $website){
            if($website->getName() == "Main Website"){
                return false;
            }
        }
        return true;
    }

    public function getProductStartingprice($product)
    {
        if($product->getTypeId() === 'bundle')
        {
            return Mage::getModel('bundle/product_price')->getTotalPrices($product,'min',1);
        }

        return $product->getFinalPrice();
    }

}