<?php

use Zip\ApiException;

class Zip_Payment_IndexController extends Mage_Core_Controller_Front_Action
{   

    /**
     * Zip Payment Landing Page
     */
    public function indexAction() {

        $isLandingPageEnabled = Mage::getSingleton('zip_payment/config')->getFlag(Zip_Payment_Model_Config::CONFIG_LANDING_PAGE_ENABLED_PATH);

        if(!Mage::helper('zip_payment')->isActive() || !$isLandingPageEnabled) {
            return null; 
        }

        $this->loadLayout();

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

        if($breadcrumbs) {

            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Home'),
                'link'  => Mage::getBaseUrl()
            ))
            ->addCrumb('zip_payment', array(
                'label' => $this->__('Zip Payment'),
                'title' => $this->__('Zip Payment')
            ));
        }

        $this->renderLayout();
    }

}