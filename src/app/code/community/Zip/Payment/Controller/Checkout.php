<?php

/**
 * Checkout controller model
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Controller_Checkout extends Mage_Core_Controller_Front_Action
{

    /**
     * @var Zip_Payment_Model_Logger
     */
    protected $logger = null;

    /**
     * Retrieve model helper
     *
     * @return Zip_Payment_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('zip_payment');
    }

    /**
     * Get logger object
     *
     * @return Zip_Payment_Model_Logger
     */
    public function getLogger()
    {
        if ($this->logger == null) {
            $this->logger = Mage::getSingleton('zip_payment/logger');
        }

        return $this->logger;
    }


    /**
     * handle redirect after response been processed
     */
    protected function redirectAfterResponse($response)
    {
        $this->getHelper()->unsetCheckoutSessionData();

        if (!isset($response['redirect_url']) || empty($response['redirect_url'])) {
            return;
        }

        // if it's an ajax call
        if (Mage::app()->getRequest()->isAjax()) {
            $response['redirect_url'] = Mage::getUrl($response['redirect_url'], array('_secure' => true));
            $this->getHelper()->returnJsonResponse($response);
        } else {
            $this->_redirect($response['redirect_url'], array('_secure' => true));
        }

    }



    /**
     * create breadcrumb for checkout pages
     */
    protected function createBreadCrumbs($key, $label)
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home', array(
                    'label' => $this->__('Home'),
                    'title' => $this->__('Home'),
                    'link'  => Mage::getBaseUrl()
                )
            );

            $isLandingPageEnabled = $this->getHelper()->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_LANDING_PAGE_ENABLED_PATH);

            if ($isLandingPageEnabled) {
                $breadcrumbs->addCrumb(
                    Zip_Payment_Model_Config::LANDING_PAGE_URL_IDENTIFIER, array(
                        'label' => $this->__('About Zip Payment'),
                        'title' => $this->__('About Zip Payment'),
                        'link'  => $this->getHelper()->getUrl(Zip_Payment_Model_Config::LANDING_PAGE_URL_ROUTE)
                    )
                );
            }

            $breadcrumbs->addCrumb(
                $key, array(
                    'label' => $this->__($label),
                    'title' => $this->__($label)
                )
            );
        }
    }
}
