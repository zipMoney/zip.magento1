<?php

/**
 * Block class of admin group
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Adminhtml_System_Config_Fieldset_Group
extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_noticeTemplate = 'zip/payment/system/config/fieldset/group/notice.phtml';

    protected $_notificationFeedModel = null;
    protected $_currentVersion = '';
    protected $_notificationData = array();

    protected function _construct()
    {
        $this->_notificationFeedModel = Mage::getSingleton('zip_payment/adminhtml_notification_feed');
        $this->_currentVersion = Mage::helper('zip_payment')->getCurrentVersion();
        $this->_notificationData = $this->_notificationFeedModel->getFeedData();

        parent::_construct();
    }

    protected function _getHeaderCommentHtml($element)
    {
        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->_noticeTemplate);
        $block->setData(
            array(
                'version_notification' => $this->_notificationFeedModel->getVersionUpgradeNotification(),
                'latest_news' => $this->getLatestNews()
            )
        );

        return $block->toHtml();
    }

    /**
     * get latest news from news feed
     */
    protected function getLatestNews()
    {
        $notificationField = Zip_Payment_Model_Adminhtml_Notification_Feed::NOTIFICATION_FIELD;

        if (isset($this->_notificationData[$notificationField])) {
            $feedData = array_reverse($this->_notificationData[$notificationField]);

            if (!empty($feedData)) {
                return $feedData[0];
            }
        }

        return null;
    }

    /**
     * Return collapse state
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _getCollapseState($element)
    {
        $extra = Mage::getSingleton('admin/session')->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        if ($element->getExpanded() !== null) {
            return 1;
        }

        return false;
    }

}
