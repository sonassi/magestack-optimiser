<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Adminhtml_OptimiserController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init actions
     *
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('system/magestack')
            ->_addBreadcrumb(Mage::helper('magestack_optimiser')->__('MageStack'), Mage::helper('magestack_optimiser')->__('MageStack'))
            ->_addBreadcrumb(Mage::helper('magestack_optimiser')->__('Optimizer'), Mage::helper('magestack_optimiser')->__('Optimizer'))
        ;

        return $this;
    }

    public function srvAction()
    {
        $this->_title($this->__('MageStack'))
            ->_title($this->__('Optimizer'))
            ->_title($this->__('Server Response Time'));

        $this->_initAction();

        $this->renderLayout();
    }

    public function tltAction()
    {
        $this->_title($this->__('MageStack'))
            ->_title($this->__('Optimizer'))
            ->_title($this->__('Total Load Time'));

        $this->_initAction();

        $this->renderLayout();
    }

    public function resolveAction()
    {
        $conditionKey = $this->getRequest()->getParam('condition_key');
        $conditionCategory = $this->getRequest()->getParam('category');

        $conditionSkip = $this->getRequest()->getParam('skip');

        try {
            $condition = Mage::helper('magestack_optimiser')->getConditionByKey($conditionKey, $conditionCategory);
            if ($condition === false) {
                Mage::throwException('1003: No condition found');
            }
            if (is_null($conditionSkip)) {

                $result = $condition->runConditionAction();
                if ($result === false) {
                    Mage::throwException('1004: No automatic action found');
                }
            }
            else {
                $condition->setConfigValue((int)$conditionSkip);
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magestack_optimiser')->__('The issue has been resolved successfully.'));
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('magestack_optimiser')->__('An error occurred while trying to resolve the issue, try manual resolve (%s)', $e->getMessage()));
        }

        $this->_redirectReferer();
    }
}