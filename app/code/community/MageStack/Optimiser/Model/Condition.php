<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Model_Condition extends Mage_Core_Model_Abstract
{
    public function getCallbackFunction($type = null)
    {
        $key = $this->getKey();
        if (!empty($type))
            $key .= "_{$type}";

        $name = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));

        return $name;
    }

    public function importFromItem($item)
    {
        $this->setData((array)$item);

        if (!$this->getConditionCheck())
            return false;

        $status = $this->getConditionCheck();
        $link = $this->getLink();

        return $this;
    }

    public function getConditionCheck()
    {
        $functionName = $this->getCallbackFunction('check');
        $helper = Mage::helper('magestack_optimiser/conditions');

        if (!method_exists($helper, $functionName))
            return -1;

        return $helper->$functionName();
    }

    public function hasConditionAction()
    {
        $functionName = $this->getCallbackFunction('action');
        $helper = Mage::helper('magestack_optimiser/actions');

        return method_exists($helper, $functionName);
    }

    public function runConditionAction()
    {
        $functionName = $this->getCallbackFunction('action');
        $helper = Mage::helper('magestack_optimiser/actions');

        if (!method_exists($helper, $functionName))
            return false;

        return $helper->$functionName();
    }

    public function isSatisfied()
    {
        return $this->getConditionCheck() == 1;
    }
}