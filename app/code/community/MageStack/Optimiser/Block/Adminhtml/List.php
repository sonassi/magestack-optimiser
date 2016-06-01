<?php
/*
* @category    Module
* @package     MageStack_Optimiser
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Optimiser_Block_Adminhtml_List extends Mage_Adminhtml_Block_Widget_Container
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

    public function getConditions()
    {
        return Mage::helper('magestack_optimiser')->getConditions();
    }

    public function getResolveUrl($condition)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/optimiser/resolve', array('condition_key' => $condition->getKey()));
    }

    public function getConditionLink($condition)
    {
        return sprintf('https://www.sonassi.com/%s', $condition->getLink());
    }
}
