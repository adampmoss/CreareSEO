<?php   
class Creare_CreareSeoSitemap_Block_Sitemap extends Mage_Core_Block_Template
{   

	public function getCreareCMSPages(){
		
		$storeId = $this->helper('core')->getStoreId(); // thanks to drawandcode for this
		$cms = Mage::getModel('cms/page')->getCollection()
										 ->addFieldToFilter('is_active',1)
										 ->addFieldToFilter('identifier',array(array('nin' => array('no-route','enable-cookies'))))
										 ->addStoreFilter($storeId);
		$url = Mage::getBaseUrl();
		$html = "";
		foreach($cms as $cmspage):
			$page = $cmspage->getData();	
			if($page['identifier'] == "home"){
				$html .= "<li><a href=\"$url\" title=\"".$page['title']."\">".$page['title']."</a></li>\n";
			} else {
				$html .= "<li><a href=\"$url".$page['identifier']."\" title=\"".$page['title']."\">".$page['title']."</a></li>\n";
			}
		endforeach;
		
		return $html;	
	} 

}