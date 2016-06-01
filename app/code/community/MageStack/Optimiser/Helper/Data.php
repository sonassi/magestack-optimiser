<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConditions()
    {
        $path = Mage::getModuleDir('etc', 'MageStack_Optimiser');
        $file = 'conditions.xml';

        $xmlPath = $path . DS . $file;

        $xmlObj = new Varien_Simplexml_Config($xmlPath);
        $xmlData = $xmlObj->getNode();

        $result = array();

        foreach ($xmlData as $item) {

            $itemObj = Mage::getModel('magestack_optimiser/condition');
            $status = $itemObj->importFromItem($item);

            $result[] = $itemObj;
        }

        return $result;
    }

    public function getConditionByKey($key)
    {
        $conditions = $this->getConditions();

        foreach ($conditions as $condition) {
            if ($condition->getKey() == $key)
                return $condition;
        }

        return false;
    }
}