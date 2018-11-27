<?php

class Zip_Payment_Block_Adminhtml_System_Config_Fieldset_Group extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    protected function _getHeaderCommentHtml($element)
    {
        $groupConfig = $this->getGroup($element)->asArray();

        if (empty($groupConfig['learn_more_link']) || !$element->getComment()) {
            return parent::_getHeaderCommentHtml($element);
        }

        $html = '<div class="comment">' . $element->getComment()
            . ' <a target="_blank" href="' . $groupConfig['learn_more_link'] . '">'
            . Mage::helper('zip_payment')->__('Learn More') . '</a></div>';

        return $html;
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
