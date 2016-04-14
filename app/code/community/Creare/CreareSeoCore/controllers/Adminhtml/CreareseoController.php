<?php

class Creare_CreareSeoCore_Adminhtml_CreareseoController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('system/creareseo/defaultseo');
	}

	public function checkAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
}
