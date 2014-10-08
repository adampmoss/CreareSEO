<?php
class Creare_CreareSeoCore_Model_Source_AnalyticsType
{
  public function toOptionArray()
  {
    return array(
      array('value' => 0, 'label' => Mage::helper('creareseocore')->__('Standard Google Analytics (default)')),
      array('value' => 1, 'label' => Mage::helper('creareseocore')->__('Universal Analytics'))
    );
  }
}