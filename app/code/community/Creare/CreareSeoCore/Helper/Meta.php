<?php
class Creare_CreareSeoCore_Helper_Meta extends Mage_Core_Helper_Abstract
{
    public function getDefaultTitle($pagetype)
    {
        $title = $this->config($pagetype.'_title');
        return $this->shortcode($title);
    }

    public function getDefaultMetaDescription($pagetype)
    {
        $metadesc = $this->config($pagetype.'_metadesc');
        return $this->shortcode($metadesc);
    }

    public function getPageType()
    {
        $registry = new Varien_Object;

        if (Mage::registry('current_product'))
        {
            $registry->_code = 'product';
            $registry->_model = Mage::registry('current_product');

            return $registry;

        } elseif (Mage::registry('current_category'))
        {
            $registry->_code = 'category';
            $registry->_model = Mage::registry('current_category');

            return $registry;

        } elseif (Mage::app()->getFrontController()->getRequest()->getRouteName() === 'cms')
        {
            $registry->_code = 'cms';
            $registry->_model = Mage::getSingleton('cms/page');

            return $registry;

        } else {
            return false;

        }
    }

    public function config($path)
    {
        return Mage::getStoreConfig('creareseocore/metadata/'.$path);
    }

    public function shortcode($string)
    {
        $pagetype = $this->getPageType();

        preg_match_all("/\[(.*?)\]/", $string, $matches);

            for($i = 0; $i < count($matches[1]); $i++)
            {
                $tag = $matches[1][$i];

                if ($tag === "store")
                {
                    $string = str_replace($matches[0][$i], Mage::app()->getStore()->getName(), $string);
                } else {

                switch ($pagetype->_code)
                {
                    case 'product' :
                        $attribute = $this->productAttribute($pagetype->_model, $tag);
                    break;

                    case 'category' :
                        $attribute = $this->attribute($pagetype->_model, $tag);
                    break;

                    case 'cms' :
                        $attribute = $this->attribute($pagetype->_model, $tag);
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
                $data = $categories->getFirstItem()->getName();
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
         if ($model->getData($attribute))
         {
            return $model->getData($attribute);
         }
     }
}