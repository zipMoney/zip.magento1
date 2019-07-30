<?php

/**
 * Configuration model for payment display mode
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_DisplayMode
{

    const DISPLAY_MODE_REDIRECT = 'redirect';
    const DISPLAY_MODE_LIGHTBOX = 'lightbox';

    /**
     * Returns the display mode option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::DISPLAY_MODE_REDIRECT,
                'label' => Mage::helper('zip_payment')->__('Redirect')
            ),
            array(
                'value' => self::DISPLAY_MODE_LIGHTBOX,
                'label' => Mage::helper('zip_payment')->__('Lightbox')
            )
        );
    }

}
