<?php

class Creare_CreareSeoCore_Model_Sitemap_Image
{

    public function create()
    {
        $alreadyUsedFilePaths = array();
        $stores               = Mage::app()->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getId();

            //to get the right product url we have to set the store
            $appEmulation           = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
            if ( ! Mage::getStoreConfigFlag('creareseocore/imagesitemap/enabled', $storeId)) {
                continue;
            }

            $filePath = Mage::getBaseDir('media') . DS . Mage::getStoreConfig('creareseocore/imagesitemap/path',
                    $storeId);

            // do not override already generated sitemaps
            if (in_array($filePath, $alreadyUsedFilePaths)) {
                continue;
            }

            $fileName   = basename($filePath);
            $folderPath = dirname($filePath);

            $io = new Varien_Io_File();
            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $folderPath));

            if ($io->fileExists($fileName) && ! $io->isWriteable($fileName)) {
                continue;
            }

            $io->streamOpen($fileName);
            $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n");
            $productCollection = Mage::getModel('catalog/product')->getCollection()->setStore($storeId)
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($productCollection);

            foreach ($productCollection as $product) {
                $mediaGallery = $product->getResource()->getAttribute('media_gallery');
                $backend      = $mediaGallery->getBackend();
                $backend->afterLoad($product);
                $images     = $product->getMediaGalleryImages();
                $productUrl = $product->getProductUrl();

                if ($images->getSize()) {
                    $xml = '<url><loc>' . htmlspecialchars($productUrl) . '</loc>';
                    foreach ($images as $image) {
                        if ( ! $image->getLabel()) {
                            $imageTitle = htmlspecialchars(Mage::getResourceModel('catalog/product')
                                ->getAttributeRawValue($product->getId(), 'name', $storeId));
                        } else {
                            $imageTitle = htmlspecialchars($image->getLabel());
                        }
                        $xml .= '<image:image>';
                        $xml .= '<image:loc>' . $image->getUrl() . '</image:loc>';
                        $xml .= '<image:title>' . $imageTitle . '</image:title>';
                        $xml .= '</image:image>';
                    }
                    $xml .= '</url>' . "\n";
                    $io->streamWrite($xml);
                }
            }
            $io->streamWrite('</urlset>');
            $io->streamClose();

            $alreadyUsedFilePaths[] = $filePath;
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
    }
}
