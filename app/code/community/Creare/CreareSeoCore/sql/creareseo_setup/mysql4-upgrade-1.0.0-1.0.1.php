<?php
 
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
/**
 * Adding Different Attributes
 */
 
// adding attribute group
//$setup->addAttributeGroup('catalog_product', 'Default', 'General', 1000);
 
// the attribute added will be displayed under the group/tab Special Attributes in product edit page
$setup->addAttribute('catalog_product', 'creareseo_discontinued', array(
    'group'         => 'General',
    'type'          => 'varchar',
    'input'         => 'select',
    'backend'       => 'eav/entity_attribute_backend_array',
    'label'         => 'Discontinued',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'searchable'    => 0,
    'filterable'    => 0,
    'comparable'    => 0,
    'visible_on_front'              => 1,
    'visible_in_advanced_search'    => 0,
    'is_html_allowed_on_front'      => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option' => array (
        'value' => array(
                        'none'      => array('No Redirect (404)'),
                        'category'  => array('301 Redirect to Category'),
                        'homepage'  => array('301 Redirect to Homepage'),
                        'product'   => array('301 Redirect to Product SKU'),                                              
                    )
    ),
));

$setup->addAttribute('catalog_product', 'creareseo_discontinued_product', array(
    'group'         => 'General',
    'type'          => 'varchar',
    'input'         => 'text',
    'backend'       => '',
    'label'         => 'Redirect to Product SKU',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'searchable'    => 0,
    'filterable'    => 0,
    'comparable'    => 0,
    'visible_on_front'              => 1,
    'visible_in_advanced_search'    => 0,
    'is_html_allowed_on_front'      => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,    
));
 
$installer->endSetup();