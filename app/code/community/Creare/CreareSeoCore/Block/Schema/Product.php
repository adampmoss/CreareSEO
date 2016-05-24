<?php

class Creare_CreareSeoCore_Block_Schema_Product extends Mage_Catalog_Block_Product_View
{
    public $_productReviews;

    public function cleanString($string)
    {
        return strip_tags(addcslashes($string, '"\\/'));
    }

    public function getCurrency()
    {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    public function getCurrencySymbol()
    {
        return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
    }

    public function getProductReviews()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Review')) {
            return array(); //keep PHP 5.3 compatibility
        }

        return $this->_productReviews = Mage::getModel('review/review')->getCollection()
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->addEntityFilter('product', $this->getProduct()->getId())
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setDateOrder();
    }

    public function getReviewsCount()
    {
        return count($this->getProductReviews());
    }

    public function getAverageRating($reviewId)
    {
        $ratings = Mage::getModel('rating/rating_option_vote')
            ->getResourceCollection()
            ->setReviewFilter($reviewId)
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->load();

        $total_rating = 0;
        $rating_count = count($ratings);


        if ($rating_count) {
            foreach ($ratings as $rating) {
                $total_rating += $rating->getValue();
            }

            $average_rating = round($total_rating / $rating_count);

            return $average_rating;
        }

        return false;
    }
}