<?php

class Creare_CreareSeoCore_Block_Page_Html_Head_Cmscanonical extends Mage_Core_Block_Template
{
    public function getCanonicalUrl()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $url = Mage::getSingleton('core/url')->parseUrl($currentUrl);
        $path = $url->getPath();
        $host = $url->getHost();

        if (Mage::getStoreConfig('web/seo/use_rewrites'))
        {
            $path = str_replace("/index.php", "", $url->getPath());
        }

        return 'http://'.$host.$path;
    }
}