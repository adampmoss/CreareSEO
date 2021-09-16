<?php

class Creare_CreareSeoCore_Model_Sitemap_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    protected $_eventPrefix = 'creareseo_xml_sitemap';
    const     ITEM_LIMIT = 5000;
    protected $_io;
    protected $_subfiles = array();

    public function generateXml()
    {

        $limit = self::ITEM_LIMIT;
        $this->fileCreate();
        $storeId = $this->getStoreId();
        $date = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string) Mage::getStoreConfig('sitemap/category/changefreq');
        $priority = (string) Mage::getStoreConfig('sitemap/category/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);

        /**
         * Delete old category files
         */
            foreach(glob($this->getPath() . substr($this->getSitemapFilename(), 0, strpos($this->getSitemapFilename(), '.xml')) . '_cat_*.xml') as $f) {
                unlink($f);
            }

        /**
         * Brake to pages
         */
        $pages = ceil( count($collection) / $limit );
        $i = 0;
        while( $i < $pages ) {
            $name = '_cat_' . $i . '.xml';
            $this->subFileCreate($name);
            $subCollection = array_slice($collection, $i * $limit, $limit);
            foreach ($subCollection as $item) {
                $_category = Mage::getModel("catalog/category")->load($item->getId());
                $title = str_replace('&', '', $_category->getName());
                $xmlImg = '';
                $_imgHtml   = '';
                if ($_imgUrl = $_category->getImageUrl()) {
                    $filename = $_category->getImageUrl();
                    $xmlImg .= '<image:image><image:loc>' . $filename . '</image:loc><image:title><![CDATA[' . $title . ']]></image:title></image:image>' . "\n";
                }


                $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority>%s</url>' . "\n",
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $changefreq,
                    $priority,
                    $xmlImg
                );
                $this->sitemapSubFileAddLine($xml, $name);
            }
            $this->subFileClose($name);
            /**
             * Add link of the subfile to the main file
             */
            $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>' . "\n", htmlspecialchars( $this->getSubFileUrl($name)), $date);
            $this->sitemapFileAddLine($xml);
            $i++;
        }

        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string) Mage::getStoreConfig('sitemap/product/changefreq');
        $priority = (string) Mage::getStoreConfig('sitemap/product/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);

        /**
         * Delete old products files
         */

            foreach(glob($this->getPath() . substr($this->getSitemapFilename(), 0, strpos($this->getSitemapFilename(), '.xml')) . '_prod_*.xml') as $f) {
                unlink($f);
            }

        /**
         * Brake to pages
         */
        $pages = ceil( count($collection) / $limit );
        $i = 0;
        while( $i < $pages ) {
            $name = '_prod_' . $i . '.xml';
            $this->subFileCreate($name);
            $subCollection = array_slice($collection, $i * $limit, $limit);
            foreach ($subCollection as $item) {
                $_product = Mage::getModel("catalog/product")->load($item->getId());
                $title = str_replace('&', '', $_product->getName());
                $galleryData = $_product->getData('media_gallery');
                $xmlImg = '';
                foreach ($galleryData['images'] as $image) {
                    $filename = htmlspecialchars(Mage::getBaseUrl('media') . 'catalog/product' . $image['file']);
                    $xmlImg .= '<image:image><image:loc>' . $filename . '</image:loc><image:title><![CDATA[' . $title . ']]></image:title></image:image>' . "\n";
                }
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority>%s</url>' . "\n",
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $changefreq,
                    $priority,
                    $xmlImg
                );
                $this->sitemapSubFileAddLine($xml, $name);
            }
            $this->subFileClose($name);
            /**
             * Add link of the subfile to the main file
             */
            $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>' . "\n", htmlspecialchars($this->getSubFileUrl($name)), $date);
            $this->sitemapFileAddLine($xml);
            $i++;
        }

        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string) Mage::getStoreConfig('sitemap/page/changefreq');
        $priority = (string) Mage::getStoreConfig('sitemap/page/priority');
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);

        /**
         * Delete old cms pages files
         */

            foreach(glob($this->getPath() . substr($this->getSitemapFilename(), 0, strpos($this->getSitemapFilename(), '.xml')) . '_pages_*.xml') as $f) {
                unlink($f);
            }

        /**
         * Brake to pages
         */
        $pages = ceil( count($collection) / $limit );
        $i = 0;
        while( $i < $pages ) {
            $name = '_cms_' . $i . '.xml';
            $this->subFileCreate($name);
            $subCollection = array_slice($collection, $i * $limit, $limit);
            foreach ($subCollection as $item) {
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>' . "\n",
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $item->getUrl() == 'home' ? 'always' : $changefreq,
                    $item->getUrl() == 'home' ? '1' : $priority
                );
                $this->sitemapSubFileAddLine($xml, $name);
            }
            $this->subFileClose($name);
            /**
             * Adding link of the subfile to the main file
             */
            $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>' . "\n", htmlspecialchars($this->getSubFileUrl($name)), $date);
            $this->sitemapFileAddLine($xml);
            $i++;
        }
        unset($collection);

        $this->fileClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    /**
     * Create sitemap subfile by name in sitemap directory
     *
     * @param $name
     */
    protected function subFileCreate($name)
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->streamOpen( substr($this->getSitemapFilename(), 0, strpos($this->getSitemapFilename(), '.xml')) . $name);

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'skin/frontend/base/default/creareseo/main-sitemap.xsl"?>' . "\n");
        $io->streamWrite('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

        $this->_subfiles[$name] = $io;
    }

    /**
     * Add line to sitemap subfile
     *
     * @param $xml
     * @param $name
     */
    public function sitemapSubFileAddLine($xml, $name) {
        $this->_subfiles[$name]->streamWrite($xml);
    }

    /**
     * Create main sitemap file
     */
    protected function fileCreate() {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'skin/frontend/base/default/creareseo/main-sitemap.xsl"?>' . "\n");
        $io->streamWrite('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
        $this->_io = $io;
    }

    /**
     * Add closing tag and close sitemap file
     */
    protected function fileClose() {
        $this->_io->streamWrite('</sitemapindex>');
        $this->_io->streamClose();
    }

    /**
     * Add closing tag and close sitemap subfile by the name
     *
     * @param $name
     */
    protected function subFileClose($name) {
        $this->_subfiles[$name]->streamWrite('</urlset>');
        $this->_subfiles[$name]->streamClose();
    }

    /**
     * Get URL of sitemap subfile by the name
     *
     * @param $name
     * @return string
     */
    public function getSubFileUrl($name)
    {
        $fileName = substr($this->getSitemapFilename(), 0, strpos($this->getSitemapFilename(), '.xml')) . $name;
        $filePath = Mage::app()->getStore($this->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $this->getSitemapPath();
        $filePath = str_replace('//','/',$filePath);
        $filePath = str_replace(':/','://',$filePath);
        return $filePath . $fileName;
    }

    /**
     * Add line to the main file
     *
     * @param $xml
     */
    public function sitemapFileAddLine($xml)
    {
        $this->_io->streamWrite($xml);
    }
}