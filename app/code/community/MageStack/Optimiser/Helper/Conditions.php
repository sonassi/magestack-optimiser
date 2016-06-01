<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Helper_Conditions extends MageStack_Optimiser_Helper_Abstract
{
    protected function checkConfigValue($path, $value, $condition = false)
    {
        $tableName = $this->getConfigTableName();

        if (!$condition)
            $condition = "value != {$value}";

        $configs = $this->getReadConnection()->fetchAll("SELECT * FROM {$tableName} WHERE path = '{$path}' AND {$condition}");

        if (!count($configs))
            return true;

        $result = array();
        foreach ($configs as $data) {
            $result[] = "Set to {$data['value']} on {$data['scope']} scope (Scope ID: {$data['scope_id']})";
        }

        return $result;
    }

    public function flatCategoryCheck()
    {
        return $this->checkConfigValue(self::FLAT_CATEGORY_CONFIG, 1);
    }

    public function flatProductCheck()
    {
        return $this->checkConfigValue(self::FLAT_PRODUCT_CONFIG, 1);
    }

    public function storeCachesCheck()
    {
        $types = Mage::app()->useCache();

        foreach ($types as $key => $value) {
            if (in_array($key, $this->_requiredCaches) && (int)$value == 0)
               return false;
        }

        return true;
    }

    public function topNavDepthCheck()
    {
        return $this->checkConfigValue(self::TOP_NAV_DEPTH_CONFIG, 0, "(value = 0 OR value > 3)");
    }

    public function redisCacheCheck()
    {
        return Mage::helper('core')->isModuleEnabled('Cm_RedisSession');
    }

    public function storeLoggingCheck()
    {
        return $this->checkConfigValue(self::STORE_LOGGING_CONFIG, 0);
    }

    public function customerLoggingCheck()
    {
        return Mage::helper('core')->isModuleEnabled('Yireo_DisableLog');
    }

    public function profilerCheck()
    {
        return $this->checkConfigValue(self::PROFILER_CONFIG, 0);
    }
}