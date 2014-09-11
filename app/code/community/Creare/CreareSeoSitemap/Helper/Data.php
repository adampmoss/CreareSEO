<?php

class Creare_CreareSeoSitemap_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function showCMS()
	{
		return Mage::getStoreConfig('creareseositemap/sitemap/showcms');
	}

	public function showCategories()
	{
		return Mage::getStoreConfig('creareseositemap/sitemap/showcategories');
	}

	public function showXMLSitemap()
	{
		return Mage::getStoreConfig('creareseositemap/sitemap/showxml');
	}

	public function showAccount()
	{
		return Mage::getStoreConfig('creareseositemap/sitemap/showaccount');
	}

	public function showContact()
	{
		return Mage::getStoreConfig('creareseositemap/sitemap/showcontact');
	}
        
        public function fullWidth()
	{
		return Mage::getStoreConfig('creareseositemap/sitemap/fullwidth');
	}
}