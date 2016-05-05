<?php

/*
* @category    Module
* @package     MageStack_Varnish
*/

require_once('Mage/Adminhtml/controllers/CacheController.php');
class MageStack_Varnish_CacheController extends Mage_Adminhtml_CacheController {

    /**
     * Overwrites Mage_Adminhtml_CacheController massRefreshAction
     */
    public function massRefreshAction()
    {
        $types = $this->getRequest()->getParam('types');

        if (Mage::helper('varnish')->useVarnishCache()) {
            if ((is_array($types) && in_array('varnish', $types)) || $types == 'varnish') {
                Mage::helper('varnish')->purgeEverything();
                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('Varnish cache type purged.'));
            }
        }

        parent::massRefreshAction();
    }
}
