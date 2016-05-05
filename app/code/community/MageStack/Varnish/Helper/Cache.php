<?php

/*
* @category    Module
* @package     MageStack_Varnish
*/

class MageStack_Varnish_Helper_Cache extends Mage_Core_Helper_Abstract
{

    /**
     * Retrieves current cookie.
     *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        return Mage::app()->getCookie();
    }

    /**
     * Check nocache stable status
     *
     * @return boolean
     */
    public function isNoCacheStable()
    {
        return $this->getCookie()->get('nocache_stable') === 1;
    }

    /**
     * Set nocache stable cookie
     *
     * @return MageStack_Varnish_Helper_Cache
     */
    public function setNoCacheStable($value = 1)
    {
        $this->getCookie()->set('nocache_stable', $value);

        return $this;
    }

    /**
     * Turn off Varnish completely
     *
     * @return MageStack_Varnish_Helper_Cache
     */
    public function turnOffVarnish()
    {
        Mage::app()->getResponse()->setHeader('MageStack-Cacheable', 'no', true);
        $this->getCookie()->set('nocache', 1);

        return $this;
    }

    /**
     * Enable Varnish cache on all pages
     *
     * @return MageStack_Varnish_Helper_Cache
     */
    public function turnOnVarnish()
    {
        if ($this->getCookie()->get('nocache'))
            $this->getCookie()->delete('nocache');

        return $this;
    }

    /**
     * Skip varnish on current page
     *
     * @return MageStack_Varnish_Helper_Cache
     */
    public function skipVarnish()
    {
        Mage::app()->getResponse()->setHeader('MageStack-Cacheable', 'no', true);

        return $this;
    }

    /**
     * Check if current URL matches with any exception set in the backend
     *
     * @return boolean
     */
    public function matchException()
    {
        $excludesConfig = Mage::getStoreConfig('varnish/options/excluded_uris');
        $excludes = unserialize($excludesConfig);

        if (!is_array($excludes))
            return false;

        $currentUri = $_SERVER['REQUEST_URI'];

        foreach ($excludes as $exclude) {
            if(stripos($currentUri, $exclude['regexp']) !== false)
                return true;
        }

        return false;
    }

    /**
     * Check if there is any item in the cart
     *
     * @return boolean
     */
    public function quoteHasItems()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        return $quote instanceof Mage_Sales_Model_Quote && $quote->hasItems();
    }
    /**

     * Check if there is any item in the compare
     *
     * @return boolean
     */
    public function hasCompareItems()
    {
        return Mage::helper('catalog/product_compare')->getItemCount() > 0;
    }

    /**
     * Check if the customer is currently logged in
     *
     * @return boolean
     */
    public function isCustomerLoggedIn()
    {
        $customerSession = Mage::getSingleton('customer/session');

        return $customerSession instanceof Mage_Customer_Model_Session && $customerSession->isLoggedIn();
    }

    /**
     * Check if the community poll has been voted
     *
     * @return boolean
     */
    public function pollVerification()
    {
        $justVotedPollId = (int) Mage::getSingleton('core/session')->getJustVotedPoll();

        return ($justVotedPollId) ? true: false;
    }
}

