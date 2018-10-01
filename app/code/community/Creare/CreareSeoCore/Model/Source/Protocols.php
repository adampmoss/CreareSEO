<?php
class Creare_CreareSeoCore_Model_Source_Protocols
{
  public function toOptionArray()
  {
    return array(
      array('value' => 'http://', 'label' => Mage::helper('creareseocore')->__('http://')),
      array('value' => 'https://', 'label' => Mage::helper('creareseocore')->__('https:// (secure)'))
    );
  }
}