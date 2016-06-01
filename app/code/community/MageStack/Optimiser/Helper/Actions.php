<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Helper_Actions extends MageStack_Optimiser_Helper_Abstract
{
    protected function setConfigValue($path, $value)
    {
        $tableName = $this->getConfigTableName();

        $result = $this->getWriteConnection()->update($tableName, array('value'=> $value), "path = '{$path}'");

        return $result;
    }

    public function storeCachesAction()
    {
        $types = Mage::app()->useCache();

        foreach ($types as $key => $value)
            if (in_array($key, $this->_requiredCaches) && (int)$value == 0)
                $types[$key] = 1;

        Mage::app()->saveUseCache($types);

        return true;
    }

    public function topNavDepthAction()
    {
        return $this->setConfigValue(self::TOP_NAV_DEPTH_CONFIG, 2);
    }

    public function storeLoggingAction()
    {
        return $this->setConfigValue(self::STORE_LOGGING_CONFIG, 0);
    }

    public function flatCategoryAction()
    {
        return $this->setConfigValue(self::FLAT_CATEGORY_CONFIG, 1);
    }

    public function flatProductAction()
    {
        return $this->setConfigValue(self::FLAT_PRODUCT_CONFIG, 1);
    }

    public function profilerAction()
    {
        return $this->setConfigValue(self::PROFILER_CONFIG, 0);
    }

}