<?php
class Creare_CreareSeoSitemap_IndexController extends Mage_Core_Controller_Front_Action
{
	
	protected function _prepareLayout() {
		
		if ($headBlock = $this->getLayout()->getBlock('head')) {
			$headBlock->setTitle("Sitemap");
		}
		return parent::_prepareLayout();			
	}
	
    public function IndexAction() {
      
	  $this->loadLayout();   
      $this->renderLayout(); 
	  
    }
}