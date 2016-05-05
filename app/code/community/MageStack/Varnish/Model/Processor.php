<?php

/*
* @category    Module
* @package     MageStack_Varnish
*/

class MageStack_Varnish_Model_Processor extends Enterprise_PageCache_Model_Processor
{

    /**
    * Get page content from cache storage
    *
    * @param string $content
    * @return string|false
    */
    public function extractContent($content)
    {
        if (!$_SERVER['IS_SONASSI'])
            return parent::extractContent($content);

        Mage::app()->initSpecified('', null, array(), array('MageStack_Varnish'));

        $observer = new Varien_Event_Observer();
        Mage::getModel('varnish/observer')->varnish($observer);

        $nocache = (Mage::app()->getCookie()->get('nocache')) ? Mage::app()->getCookie()->get('nocache') : false;
        $content = parent::extractContent($content);

        // Restore nocache cookie status
        if ($nocache !== false)
            Mage::app()->getCookie()->set('nocache', $nocache);

        return $content;
    }

}
