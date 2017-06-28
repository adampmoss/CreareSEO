<?php

class Creare_CreareSeoCore_Block_Page_Html_Head_Cmscanonical extends Mage_Core_Block_Template
{
    public function getCanonicalUrl()
    {
        $cmsPagePath = Mage::getSingleton('cms/page')->getIdentifier();
        $isHomePage = $this->getUrl('') == $this->getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
        if($isHomePage){
			$canonicalUrl = Mage::getBaseUrl();
		} else {
			$canonicalUrl = Mage::getBaseUrl().$cmsPagePath;
		}        
        $protocol = $this->creareseoHelper()->getConfig('cms_canonical_protocol');

        $canonicalUrl = str_replace(
            array("http://", "https://"),
            $protocol,
            $canonicalUrl
        );

        if (Mage::helper('core')->isModuleEnabled('Wyomind_Cmstree') && ! $isHomePage) {
            $canonicalUrl .= Mage::helper('cmstree')->getUrlSuffix();
        }

        $addSlash = $this->creareseoHelper()->getConfig('cms_canonical_slashes');

        if ($addSlash) {
            $canonicalUrl .= substr($canonicalUrl, -1) !== '/'
                ? "/"
                : "";
        } else {
            $canonicalUrl = substr($canonicalUrl, -1) == '/'
                ? substr($canonicalUrl, 0, -1)
                : $canonicalUrl;
        }

        return $canonicalUrl;
    }

    /**
     * @return Creare_CreareSeoSitemap_Helper_Data
     */
    public function creareseoHelper()
    {
        return Mage::helper("creareseocore");
    }
}
