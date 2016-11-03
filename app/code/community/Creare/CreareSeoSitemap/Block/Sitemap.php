<?php class Creare_CreareSeoSitemap_Block_Sitemap extends Mage_Core_Block_Template
{
    protected $cmsPages;
    protected $categoryTreeHtml = "";
    protected $otherPages;
    protected $store;

    public function __construct(array $args)
    {
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();

        $this->store = Mage::app()->getStore();
        $this->setCmsPages();
        $this->buildCategoryTreeHtml($rootCategoryId, false);

        parent::__construct($args);
    }

    private function setCmsPages()
    {
        $this->cmsPages = Mage::getModel('cms/page')
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('identifier',
                array(
                    array('nin' =>
                        array('no-route','enable-cookies')
                    )
                )
            )
            ->addStoreFilter($this->store->getId());
    }

    public function getCmsPages()
    {
        return $this->cmsPages;
    }

    public function buildCategoryTreeHtml($parentId, $isChild)
    {
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect(array('url', 'name'))
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('parent_id',array('eq' => $parentId));

        $class = ($isChild) ? "subcategories" : "top-level";

        $this->categoryTreeHtml .= '<ul class="'.$class.'">';
        foreach ($categories as $category)
        {
            $this->categoryTreeHtml .= '<li><a href="'.$category->getUrl().'" >'.$category->getName()."</a>";
            $children = $category->getChildren();
            if($children) {
                $this->categoryTreeHtml .= $this->buildCategoryTreeHtml($category->getId(), true);
            }
            $this->categoryTreeHtml .= '</li>';
        }
        $this->categoryTreeHtml .= '</ul>';
    }

    /**
     * @return string
     */
    public function getCategoryTreeHtml()
    {
        return $this->categoryTreeHtml;
    }
}