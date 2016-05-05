<?php
/*
* @category    Module
* @package     MageStack_Varnish
* @copyright   Copyright (c) 2016 Sonassi
*/

class MageStack_Varnish_EsiController extends Mage_Core_Controller_Front_Action
{

	public function indexAction() {

		$response = array();
		$response['sid'] = Mage::getModel('core/session')->getEncryptedSessionId();

		if ($currentProductId = $this->getRequest()->getParam('currentProductId')) {
			Mage::getSingleton('catalog/session')->setLastViewedProductId($currentProductId);
		}

		$this->loadLayout();
		$layout = $this->getLayout();

		$requestedBlockName = $this->getRequest()->getParam('block');

		$tmpBlock = $layout->getBlock($requestedBlockName);
		if ($tmpBlock) {
			$response['block'] = $tmpBlock->toHtml();
		} else {
			$response['block'] = 'BLOCK NOT FOUND';
		}

		$this->getResponse()->setBody($response['block']);
	}

}