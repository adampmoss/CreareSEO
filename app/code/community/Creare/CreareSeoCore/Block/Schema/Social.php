<?php

class Creare_CreareSeoCore_Block_Schema_Social extends Mage_Core_Block_Template
{
    public $_socialProfiles;

    public function __construct()
    {
        $this->_socialProfiles = explode("\n", Mage::getStoreConfig('creareseocore/social_schema/social_profiles'));
    }

    public function isEnabled()
    {
        return Mage::getStoreConfig('creareseocore/social_schema/enabled');
    }

    public function socialProfileCount()
    {
        return count($this->_socialProfiles);
    }
}