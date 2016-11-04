## Latest Version

1.5

## About CreareSEO for Magento

This is a free Magento extension has a range of features that helps Magento become more compliant with the latest SEO standards. The ultimate aim is for your store to rank better in Google by making small adjustments to your store's configuration. 

This extension is no longer available on Magento Connect

### Compatibility

Magento Community Edition 1.4 to 1.9
Tested successfully in most versions of Enterprise Edition

### Installation

1. Unpack the extension ZIP file in your Magento root directory
2. Clear the Magento cache: **System > Cache Management**
3. Log out the Magento admin and log back in to clear the ACL list
4. Recompile if you are using the Magento Compiler

### Usage

Our official [supporting blog post](http://www.creare.co.uk/creare-seo-magento-extension) contains a video and further instructions on how to use verion 1 of the extension. However, since the video was recorded we have made many improvements and added many new features.

Most settings can be configured in **System > CreareSEO > General Settings**

The CreareSEO Checklist page can be accessed via **System > CreareSEO > CreareSEO Checklist**. 

This page will check and make recommendations on various configuration settings within Magento and CreareSEO that should or shouldn't be enabled in order to maximise SEO performance.

![CreareSEO Checklist](https://github.com/Creare/CreareSEO/blob/master/creareseo-checklist.png)

### Features

Below is a list of the current features available within the extension. Our team of SEO experts are always up-to-date with the latest search engine standards and requirements, so this list will continue to grow over time.

|Feature|Version|
|---|---|
|CreareSEO Checklist Page|1.0|
|HTML Sitemap|1.0|
|Remove meta keywords tags|1.0|
|Remove empty meta description tags|1.0|
|NOINDEX,FOLLOW on category filters and pagination|1.0|
|Custom category headings|1.0|
|Disabled product redirects|1.0|
|Breadcrumbs structured data|1.0|
|Hide 'duplicate product' button|1.0|
|Twitter cards on product pages|1.0|
|OpenGraph data on product pages|1.0|
|.htaccess editor via configuration|1.0|
|robots.txt editor via configuration (multisite compatible)|1.0|
|Canonical product redirects|1.0|
|Custom contact page title and meta description|1.0|
|Default product page title and meta description templates|1.0|
|Default category page title and meta description templates|1.0|
|Default CMS meta description templates|1.0|
|Next/Prev meta elements on category pages for pagination|1.1|
|Force page title for homepage|1.1|
|NOINDEX robots tag added to media gallery pages|1.1|
|NOINDEX robots tag added to search results pages|1.1|
|Universal Analytics support for older Magento versions|1.2|
|SiteLinks Search added to homepage|1.2|
|Mandatory product image label validation|1.2|
|Product structured data|1.3|
|Social structured data|1.3|
|Product/Category/CMS support for Google Content Grouping|1.3|
|Google Tag Manager support|1.3|
|Canonical meta tags for CMS pages|1.4|
|Logo structured data|1.4|
|Organization structured data|1.4|

### Support

Go [here](http://creareseo.custservhq.com/articles/frequently-asked-questions) to see a list of issues that we have come across and we have found a solution to. If you have any other issues with this extension, please [open an issue](https://github.com/Creare/CreareSEO/issues) on GitHub.

### Disable the Module

To disable the module open **app/etc/modules/Creare_CreareSeoCore.xml** and **app/etc/modules/Creare_CreareSeoSitemap.xml** and in both files change this:

    <active>true</active>
to this:

    <active>false</active>

After doing this, clear the cache and reindex your data.

### Developers

- Adam Moss ([@adampmoss](https://twitter.com/adampmoss)) 
- Robert Kent ([@kent_robert](https://twitter.com/kent_robert)) (previous)
