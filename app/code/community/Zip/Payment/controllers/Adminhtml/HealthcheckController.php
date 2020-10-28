<?php

/**
 * Health Check controller
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

use \Zip\Configuration;

class Zip_Payment_Adminhtml_HealthcheckController extends Mage_Adminhtml_Controller_Action
{
    public function checkAction()
    {
        $healthCheck = Mage::getModel('zip_payment/adminhtml_system_config_backend_healthCheck');
        $result = $healthCheck->getHealthResult();
        Mage::app()->getResponse()->setBody(json_encode($result));
    }
}

