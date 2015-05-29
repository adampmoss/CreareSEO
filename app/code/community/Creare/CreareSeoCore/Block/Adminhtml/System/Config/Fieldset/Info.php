<?php

class Creare_CreareSeoCore_Block_Adminhtml_System_Config_Fieldset_Info 
	extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

		protected $_template = 'creareseo/system/config/fieldset/info.phtml';

		public function render(Varien_Data_Form_Element_Abstract $element) {
        	return $this->toHtml();
    	}

    	protected function getModuleVersion() {
        	return (string) Mage::getConfig()->getNode('modules/Creare_CreareSeoCore/version');
    	}

    	protected function getCreareWebsite() {
        	return (string) "https://www.creare.co.uk";
    	}

    	protected function getCreareHelpDesk() {
        	return (string) "http://creareseo.custservhq.com/";
    	}

    	protected function getCreareBlogPost() {
        	return (string) "https://www.creare.co.uk/creare-seo-magento-extension";
    	}

    	protected function getGitHubPage() {
        	return (string) "https://github.com/Creare/CreareSEO";
    	}
	}
