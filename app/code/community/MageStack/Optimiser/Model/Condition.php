<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Model_Condition extends Mage_Core_Model_Abstract
{
    protected $_severityScores = array('high' => 3, 'normal' => 2, 'low' => 1);

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

        $this->setSeverityScore($this->_severityScores[strtolower($this->getSeverity())]);

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
            return -2;

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

    public function getConfigPath()
    {
        return sprintf('magestack/condition/%s', $this->getKey());
    }

    public function getConfigValue()
    {
        return Mage::app()->getStore()->getConfig($this->getConfigPath());
    }

    public function setConfigValue($value)
    {
        return Mage::getConfig()->saveConfig($this->getConfigPath(), $value, 'default', 0);
    }

    public function isSatisfied()
    {
        $conditionCheck = $this->getConditionCheck();
        if ($conditionCheck === -2) {
            $configValue = $this->getConfigValue();
            return $configValue == 1 ? 1 : 0;
        }

        return $conditionCheck == 1 ? 1 : -1;
    }
}