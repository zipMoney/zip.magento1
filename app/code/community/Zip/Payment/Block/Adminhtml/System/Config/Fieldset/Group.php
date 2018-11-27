<?php

class Zip_Payment_Block_Adminhtml_System_Config_Fieldset_Group extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $headerCommentTemplate = 'zip/payment/system/config/fieldset/group/header_comment.phtml';

    protected function _getHeaderCommentHtml($element)
    {
        $groupConfig = $this->getGroup($element)->asArray();

        $block = Mage::app()->getLayout()->createBlock('core/template');
        $block->setTemplate($this->headerCommentTemplate);
        $block->setData(array(
            'comment' => $element->getComment(),
            'learn_more' =>  $groupConfig['learn_more_link']
        ));

        return $block->toHtml();
    }

    /**
     * Return collapse state
     *
     * @param Varien_Data_Form_Element_Abstract $element
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
