<?php
class Creare_CreareSeoSitemap_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
	{
	    $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Sitemap'));
        $this->renderLayout();
    }
}