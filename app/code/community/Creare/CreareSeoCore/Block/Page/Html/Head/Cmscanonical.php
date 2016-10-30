<?php

class Creare_CreareSeoCore_Block_Page_Html_Head_Cmscanonical extends Mage_Core_Block_Template
{
    public function getCanonicalUrl()
    {
        $cmsPagePath = Mage::getSingleton('cms/page')->getIdentifier();
        $canonicalUrl = Mage::getBaseUrl().$cmsPagePath;

        if (
            strpos($canonicalUrl, "/index.php") &&
            Mage::getStoreConfig('web/seo/use_rewrites')
        ) {
            $canonicalUrl = str_replace("/index.php", "", $canonicalUrl);
        }

        return $canonicalUrl;
    }
}
