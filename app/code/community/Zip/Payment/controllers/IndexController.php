<?php

/**
 * Index controller
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

use \Zip\ApiException;

class Zip_Payment_IndexController extends Mage_Core_Controller_Front_Action
{   

    /**
     * Zip Payment Landing Page
     */
    public function indexAction() {

        $helper = Mage::helper('zip_payment');

        $isLandingPageEnabled = Mage::getSingleton('zip_payment/config')->getFlag(Zip_Payment_Model_Config::CONFIG_LANDING_PAGE_ENABLED_PATH);

        if(!$helper->isActive() || !$isLandingPageEnabled) {
            return null; 
        }

        $this->loadLayout();

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

        if($breadcrumbs) {

            $breadcrumbs->addCrumb('home', array(
                'label' => $helper->__('Home'),
                'title' => $helper->__('Home'),
                'link'  => Mage::getBaseUrl()
            ))
            ->addCrumb('zip_payment', array(
                'label' => $helper->__('Zip Payment'),
                'title' => $helper->__('Zip Payment')
            ));
        }

        $this->renderLayout();
    }

}