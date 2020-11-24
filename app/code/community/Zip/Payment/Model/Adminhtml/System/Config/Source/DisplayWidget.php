<?php

/**
 * Configuration model for display widget mode
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/


class Zip_Payment_Model_Adminhtml_System_Config_Source_DisplayWidget
{

    // Zip widget will display inside the iframe
    const DISPLAY_WIDGET_IFRAME = 'iframe';
    // Zip widget will display inline
    const DISPLAY_WIDGET_INLINE = 'inline';

    /**
     * Returns the display mode option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::DISPLAY_WIDGET_IFRAME,
                'label' => Mage::helper('zip_payment')->__('Iframe')
            ),
            array(
                'value' => self::DISPLAY_WIDGET_INLINE,
                'label' => Mage::helper('zip_payment')->__('Inline')
            )
        );
    }

}
