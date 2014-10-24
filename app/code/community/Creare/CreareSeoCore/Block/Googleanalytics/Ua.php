<?php

class Creare_CreareSeoCore_Block_Googleanalytics_Ua extends Mage_GoogleAnalytics_Block_Ga
{
    /* Had to do it this way due to older versions of Magento */

     protected function _toHtml()
    {
        if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
            return '';
        }
        else {

            $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);

            $html = "\r\n<!-- BEGIN UNIVERSAL ANALYTICS CODE -->
<script type=\"text/javascript\">
//<![CDATA[
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '".$accountId."', 'auto');\r\n";
    if ($this->enableDemographics())
        {
            $html .= "ga('require', 'displayfeatures');\r\n";
        }
    $html .= "ga('send', 'pageview');\r\n";
    $html .= $this->_getOrdersTrackingCode();
    $html .= $this->getAdditionalTrackingCode();
    $html .=  "//]]>
</script>
<!-- END UNIVERSAL ANALYTICS CODE -->\r\n";

        return $html;

        }
    }

    protected function getOrderIds()
    {
        return array(Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId());
    }

	/* Universal Analytics script for ecommerce orders */

	protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();

        $result[] = "ga('require', 'ecommerce');";

        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            	$result[] = "ga('ecommerce:addTransaction', { 
            		'id': '".$order->getIncrementId()."', 
            		'affiliation': '".$this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName())."',
            		'revenue': '".$order->getBaseGrandTotal()."',
            		'shipping': '".$order->getBaseShippingAmount()."',
            		'tax': '".$order->getBaseTaxAmount()."'
            	});";
            foreach ($order->getAllVisibleItems() as $item) {

            	$result[] = "ga('ecommerce:addItem', {
            		'id': '".$order->getIncrementId()."',
            		'name': '".$this->jsQuoteEscape($item->getName())."',
            		'sku': '".$this->jsQuoteEscape($item->getSku())."',
            		'price': '".$item->getBasePrice()."',
            		'quantity': '".$item->getQtyOrdered()."'
            	});";
            }
            $result[] = "ga('ecommerce:send');";
        }
        return implode("\n", $result);
    }

    public function enableDemographics()
    {
        return Mage::getStoreConfig('creareseocore/googleanalytics/enable_demographics');
    }

    public function getAdditionalTrackingCode()
    {
        return Mage::getStoreConfig('creareseocore/googleanalytics/additional_tracking_code');
    }
}