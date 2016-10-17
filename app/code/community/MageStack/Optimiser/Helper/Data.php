<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConditions($category = false)
    {
        $path = Mage::getModuleDir('etc', 'MageStack_Optimiser');
        $file = 'conditions.xml';

        $xmlPath = $path . DS . $file;

        $xmlObj = new Varien_Simplexml_Config($xmlPath);
        $xmlData = $xmlObj->getNode();

        if ($category !== false)
            $xmlData = $xmlData->$category->item;

        $result = array();

        foreach ($xmlData as $item) {

            if (empty($item->key)) continue;

            $itemObj = Mage::getModel('magestack_optimiser/condition');
            $status = $itemObj->importFromItem($item);

            $result[] = $itemObj;
        }

        usort($result, function ($a, $b) {
            return $a->getSeverityScore() < $b->getSeverityScore();
        });

        return $result;
    }

    public function getConditionByKey($key, $category = false)
    {
        $conditions = $this->getConditions($category);

        foreach ($conditions as $condition) {
            if ($condition->getKey() == $key)
                return $condition;
        }

        return false;
    }
}