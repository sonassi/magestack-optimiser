<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Block_Adminhtml_List_Abstract extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_headerText = Mage::helper('magestack_optimiser')->__('Performance Check-List');
        parent::_construct();
    }

    /**
     * Prepare layout
     *
     */
    protected function _prepareLayout()
    {
        $this->removeButton('add');
        return parent::_prepareLayout();
    }

    public function getCategory()
    {
        return false;
    }

    public function getConditions()
    {
        return Mage::helper('magestack_optimiser')->getConditions($this->getCategory());
    }

    public function getResolveUrl($condition, $skip = false)
    {
        $params = array('condition_key' => $condition->getKey());
        if ($this->getCategory() !== false)
            $params['category'] = $this->getCategory();

        if ($skip !== false) {
            $params['skip'] = $skip;
        }

        return Mage::helper('adminhtml')->getUrl('adminhtml/optimiser/resolve', $params);
    }

    public function getConditionLink($condition)
    {
        return sprintf('https://www.sonassi.com/%s', $condition->getLink());
    }

    public function updateTotals(&$totals, $status)
    {
        $totals['count'] += 1;
        $key = $status == 1 ? 'valid' : 'invalid';
        $totals[$key] += 1;

        return true;
    }

    public function displayTotals($totals)
    {
        if(!$totals['count']) return false;

        $scorePercentage = ($totals['valid'] / $totals['count']) * 100;
        $scorePercentage = floor($scorePercentage);

        return sprintf('<div class="totals">Score: %s/%s (%s%%)</div>', $totals['valid'], $totals['count'], $scorePercentage);
    }
}
