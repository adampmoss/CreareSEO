<?php
class Creare_CreareSeoSitemap_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
	{
        $helper = Mage::helper('creareseositemap');
	    $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($helper->getConfig('page_title'));
        $this->getLayout()->getBlock('head')->setDescription($helper->getConfig('meta_description'));
        $this->renderLayout();
    }
}