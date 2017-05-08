<?php class Creare_CreareSeoSitemap_Block_Sitemap extends Mage_Core_Block_Template
{
    protected $cmsPages = false;
    protected $categoryTreeHtml = "";
    protected $otherPages = array();
    protected $store;
    protected $sitemapHelper;
    protected $xmlSitemaps = false;
    protected $_flatEnabled = array();

    public function __construct(array $args)
    {
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
        $this->store = Mage::app()->getStore();
        $this->sitemapHelper = Mage::helper('creareseositemap');
        $this->setOtherPages();
        $this->setCmsPages();
        $this->setXmlSitemaps();
        $this->buildCategoryTreeHtml($rootCategoryId);

        parent::__construct($args);
    }

    /**
     * Retrieve Catalog Product Flat Helper object
     *
     * @return Mage_Catalog_Helper_Product_Flat
     */
    public function getFlatHelper()
    {
        return Mage::helper('catalog/category_flat');
    }

    /**
     * Retrieve is flat enabled flag
     * Return always false if magento run admin
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        // Flat Data can be used only on frontend
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }
        $storeId = $this->getStoreId();
        if (!isset($this->_flatEnabled[$storeId])) {
            $flatHelper = $this->getFlatHelper();
            $this->_flatEnabled[$storeId] = $flatHelper->isAvailable() && $flatHelper->isBuilt($storeId);
        }
        return $this->_flatEnabled[$storeId];
    }

    /**
     * @return Creare_CreareSeoSitemap_Helper_Data
     */
    public function sitemapHelper()
    {
        return $this->sitemapHelper;
    }

    private function setCmsPages()
    {
        if ($this->sitemapHelper->getConfig('showcms')) {
            $this->cmsPages = Mage::getModel('cms/page')
                ->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('identifier',
                    array(
                        array('nin' =>
                            array('no-route', 'enable-cookies')
                        )
                    )
                )
                ->addStoreFilter($this->store->getId());

            $this->cmsPages = $this->cmsPages->count()
                ? $this->cmsPages
                : false;
        }

    }

    /**
     * @return mixed
     */
    public function getCmsPages()
    {
        return $this->cmsPages;
    }

    private function setXmlSitemaps()
    {
        if ($this->sitemapHelper->getConfig('showxml')) {
            $this->xmlSitemaps = Mage::getModel('sitemap/sitemap')
                ->getCollection()
                ->addStoreFilter($this->store->getId());

            $this->xmlSitemaps = $this->xmlSitemaps->count()
                ? $this->xmlSitemaps
                : false;
        }
    }

    /**
     * @return mixed
     */
    public function getXmlSitemaps()
    {
        return $this->xmlSitemaps;
    }

    private function setOtherPages()
    {

        if ($this->sitemapHelper->getConfig('showaccount')) {
            $this->otherPages[] = array(
                'url' => $this->getUrl('customer/account'),
                'title' => $this->__('My Account')
            );
        }

        if ($this->sitemapHelper->getConfig('showcontact')) {
            $this->otherPages[] = array(
                'url' => $this->getUrl('contacts'),
                'title' => $this->__('Contact Us')
            );
        }

        $this->otherPages[] = array(
            'url' => $this->getUrl('catalogsearch/advanced'),
            'title' => $this->__('Advanced Search')
        );

        $this->otherPages[] = array(
            'url' => $this->getUrl('sitemap'),
            'title' => $this->__('Sitemap')
        );
    }

    /**
     * @return array
     */
    public function getOtherPages()
    {
        return $this->otherPages;
    }

    /**
     * @param $parentId
     * @param $isChild
     */
    public function buildCategoryTreeHtml($parentId, $isChild = false)
    {
        if ($this->sitemapHelper->getConfig('showcategories')) {
            $categories = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect(array('name'))
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToFilter('parent_id', array('eq' => $parentId));

            if(!$this->isEnabledFlat()) {
                $categories->addAttributeToSelect('url');
            }

            $class = ($isChild) ? "subcategories" : "top-level";

            $this->categoryTreeHtml .= '<ul class="' . $class . '">';
            foreach ($categories as $category) {
                if($this->isEnabledFlat()) {
                    $url = Mage::helper('catalog/category')->getCategoryUrl($category);
                }else {
                    $url = $category->getUrl();
                }
                $this->categoryTreeHtml .= '<li><a href="' . $url . '" >' . $category->getName() . "</a>";
                $children = $category->getChildren();
                if ($children) {
                    $this->categoryTreeHtml .= $this->buildCategoryTreeHtml($category->getId(), true);
                }
                $this->categoryTreeHtml .= '</li>';
            }
            $this->categoryTreeHtml .= '</ul>';
        }

        $this->categoryTreeHtml = $this->categoryTreeHtml !== ""
            ? $this->categoryTreeHtml
            : false;

    }

    /**
     * @return string
     */
    public function getCategoryTreeHtml()
    {
        return $this->categoryTreeHtml;
    }
}