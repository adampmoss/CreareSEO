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
                $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $changefreq,
                    $priority
                );
                $this->sitemapSubFileAddLine($xml, $name);
            }
            $this->subFileClose($name);
            /**
             * Add link of the subfile to the main file
             */
            $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>', htmlspecialchars( $this->getSubFileUrl($name)), $date);
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
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $changefreq,
                    $priority
                );
                $this->sitemapSubFileAddLine($xml, $name);
            }
            $this->subFileClose($name);
            /**
             * Add link of the subfile to the main file
             */
            $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>', htmlspecialchars($this->getSubFileUrl($name)), $date);
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
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
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
            $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>', htmlspecialchars($this->getSubFileUrl($name)), $date);
            $this->sitemapFileAddLine($xml);
            $i++;
        }
        unset($collection);

        /**
         * Generate images sitemap
         */
        $changefreq = (string) Mage::getStoreConfig('sitemap/product/changefreq');
        $priority = (string) Mage::getStoreConfig('sitemap/product/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);

        /**
         * Delete old images files
         */

            foreach(glob($this->getPath() . substr($this->getSitemapFilename(), 0, strpos($this->getSitemapFilename(), '.xml')) . '_images_*.xml') as $f) {
                unlink($f);
            }

        /**
         * Brake to pages
         */
        $pages = ceil( count($collection) / $limit );
        $i = 0;
        while( $i < $pages ) {
            $name = '_images_' . $i . '.xml';
            $this->subFileCreate($name, true);
            $subCollection = array_slice($collection, $i * $limit, $limit);
            foreach ($subCollection as $item) {
                $_product = Mage::getModel("catalog/product")->load($item->getId());
                $title = str_replace('&', '', $_product->getName());
                $galleryData = $_product->getData('media_gallery');
                $xmlImg = '';
                foreach ($galleryData['images'] as $image) {
                    $filename = htmlspecialchars(Mage::getBaseUrl('media') . 'catalog/product' . $image['file']);
                    $xmlImg .= '<image:image><image:loc>' . $filename . '</image:loc><image:title>' . $title . '</image:title></image:image>';
                }
                if ($xmlImg != "") {
                    $xml = sprintf(
                        '<url>' . "\n" . '<loc>%s</loc>%s</url>' . "\n" . '',
                        htmlspecialchars($baseUrl . $item->getUrl()),
                        $xmlImg
                    );
                    $this->sitemapSubFileAddLine($xml, $name);
                }
            }
            $this->subFileClose($name);
            /**
             * Add link of the subfile to the main file
             */
            $xml = sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>', htmlspecialchars($this->getSubFileUrl($name)), $date);
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
    protected function subFileCreate($name, $image=false)
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->streamOpen( substr($this->getSitemapFilename(), 0, strpos($this->getSitemapFilename(), '.xml')) . $name);

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        if ($image == true) {
            $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n");
            $io->streamWrite(' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n");
        } else {
            $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
        }
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

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
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