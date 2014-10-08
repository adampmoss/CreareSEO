<?php

class Creare_CreareSeoCore_Block_GoogleAnalytics_Ua extends Mage_GoogleAnalytics_Block_Ga
{

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