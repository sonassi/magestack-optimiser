<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Adminhtml_OptimiserController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('system');
        $this->renderLayout();
    }

    public function resolveAction()
    {
        $conditionKey = $this->getRequest()->getParam('condition_key');

        $condition = Mage::helper('magestack_optimiser')->getConditionByKey($conditionKey);
        if ($condition === false) {
            //Raise an error
        }

        $result = $condition->runConditionAction();
        if ($result === false) {
            //Raise an error
        }

        $this->_redirect('*/*/');
    }
}