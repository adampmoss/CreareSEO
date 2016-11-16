<?php
class Creare_CreareSeoCore_Helper_Meta extends Mage_Core_Helper_Abstract
{
    public function getDefaultTitle($pageType)
    {
        $title = $this->config($pageType.'_title');
        return $this->shortcode($title);
    }

    public function getDefaultMetaDescription($pageType)
    {
        $metadesc = $this->config($pageType.'_metadesc');
        return $this->shortcode($metadesc);
    }

    public function getPageType()
    {
        return Mage::registry("current_pagetype")
            ? Mage::registry("current_pagetype")
            : $this->registerPageType();
    }

    public function registerPageType()
    {
        $pageType = new Varien_Object;

        if (Mage::registry('current_product')) {
            $pageType->code = 'product';
            $pageType->model = Mage::registry('current_product');

        } elseif (Mage::registry('current_category')) {
            $pageType->code = 'category';
            $pageType->model = Mage::registry('current_category');

        } elseif (Mage::app()->getFrontController()->getRequest()->getRouteName() === 'cms') {
            $pageType->code = 'cms';
            $pageType->model = Mage::getSingleton('cms/page');

        }

        if ($pageType->code) {
            Mage::register("current_pagetype", $pageType);
            return $pageType;
        }

        return false;
    }

    public function config($path)
    {
        return Mage::getStoreConfig('creareseocore/metadata/'.$path);
    }

    public function shortcode($string)
    {
        $pageType = $this->getPageType();

        preg_match_all("/\[(.*?)\]/", $string, $matches);

        for($i = 0; $i < count($matches[1]); $i++) {
            $tag = $matches[1][$i];

            if ($tag === "store") {
                $string = str_replace($matches[0][$i], Mage::app()->getStore()->getName(), $string);
            } else {

                switch ($pageType->code) {
                    case 'product' :
                        $attribute = $this->productAttribute($pageType->model, $tag);
                    break;

                    case 'category' || 'cms' :
                        $attribute = $this->attribute($pageType->model, $tag);
                    break;
                }

            $string = str_replace($matches[0][$i], $attribute, $string);

            }
        }

        return $string;
     }

    public function productAttribute($product, $attribute)
    {
        if ($attribute == "categories" || $attribute == "first_category") {

            $catIds = $product->getCategoryIds();
            $categories = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', $catIds)
                ->addIsActiveFilter();

            if ($categories->count() < 1) {
                return "";
            }

            if ($attribute == "categories") {
                $categoryNames = array();

                foreach ($categories as $category) {
                    $categoryNames[] = $category->getName();
                }

                $data = implode(", ", $categoryNames);
            }

            if ($attribute == "first_category") {
                $data = $categories->getFirstItem()
                    ->setPageSize(1)
                    ->getName();
            }

        } else if ($product->getData($attribute)) {
            $data = $product->getResource()
                ->getAttribute($attribute)
                ->getFrontend()
                ->getValue($product);
        }

        return $data;
    }

     public function attribute($model, $attribute)
     {
         $value = $model->getData($attribute);
         return $value ? $value : "";
     }

     public function cleanString($string)
     {
         return htmlspecialchars(html_entity_decode(trim(
             $string), ENT_QUOTES, 'UTF-8')
         );
     }
}