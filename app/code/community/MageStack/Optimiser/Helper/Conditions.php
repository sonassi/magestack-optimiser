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

        $select = $this->getReadConnection()->select()
            ->from(array('main_table' => $tableName))
            ->where("path = '{$path}'")
            ->where("{$condition}");

        $config = $this->getReadConnection()->fetchAll($select);

        if (!count($config))
            return true;

        $result = array();
        foreach ($config as $data) {
            $result[] = "{$path} set to {$data['value']} on {$data['scope']} scope (Scope ID: {$data['scope_id']})";
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

    public function logCleaningCheck()
    {
        $cleanEnabled = $this->checkConfigValue(self::SYSTEM_LOG_CLEANENA, 1);
        if ($cleanEnabled === true)
            return $this->checkConfigValue(self::SYSTEM_LOG_CLEANDAY, 30);

        return $cleanEnabled;
    }

    public function productListingsCheck()
    {
        $gridPerPage = $this->checkConfigValue(self::CATALOG_FRONT_GRPEP, 0, "(value > 16)");
        if ($gridPerPage !== true)
            return $gridPerPage;

        $listPerPage = $this->checkConfigValue(self::CATALOG_FRONT_LIPEP, 0, "(value > 16)");
        if ($listPerPage !== true)
            return $listPerPage;

        return $this->checkConfigValue(self::CATALOG_FRONT_ALOAL, 1);
    }

    public function indexesStatesCheck()
    {
        $indexCollection = Mage::getModel('index/process')->getCollection();

        foreach ($indexCollection as $index) {

            if (in_array($index->getIndexerCode(), $this->_allowedIndexes))
                continue;

            if ($index->getMode() == 'real_time')
                return false;
        }

        return true;
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

    public function sonassiCatfixCheck()
    {
        return Mage::helper('core')->isModuleEnabled('Sonassi_CatFix');
    }

    public function varnishCacheCheck()
    {
        return Mage::helper('core')->isModuleEnabled('MageStack_Varnish');
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