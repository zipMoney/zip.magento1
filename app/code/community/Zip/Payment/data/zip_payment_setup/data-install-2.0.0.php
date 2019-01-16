<?php

$layout = <<<EOF
<reference name="head">
    <block type="zip_payment/widget" name="zip.payment.widget.head" template="zip/payment/widgets/head.phtml"/>   
</reference>
EOF;

    
$content = <<<EOF
{{block type="zip_payment/widget" name="zip.payment.widget.root" template="zip/payment/widgets/root.phtml"}}
{{block type="core/template" name="zip.payment.landing" template="zip/payment/widgets/landing.phtml"}}
EOF;

$landingPageData = array(
    'title' => 'About Zip Payment',
    'root_template' => 'one_column',
    'meta_keywords' => 'zip, zip payment',
    'meta_description' => 'Create your account in moments and select Zip at checkout',
    'identifier' => Zip_Payment_Model_Config::LANDING_PAGE_URL_IDENTIFIER,
    'content_heading' => 'About Zip Payment',
    'stores' => array(0),//available for all store views
    'layout_update_xml' => $layout,
    'content' => $content
);

Mage::getModel('cms/page')->setData($landingPageData)->save();