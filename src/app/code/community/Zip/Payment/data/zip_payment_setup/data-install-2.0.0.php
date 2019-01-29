<?php

// allow zip payment widget in block permission
$widgetBlockName = 'zip_payment/widget';
Mage::getModel('admin/block')->load($widgetBlockName, 'block_name')
    ->setData('block_name', $widgetBlockName)
    ->setData('is_allowed', 1)
    ->save();

$content = <<<EOF
<div data-zm-asset="landingpage" data-zm-widget="inline"></div>
EOF;

// set up landing page
$landingPageData = array(
    'title' => 'About Zip Payment',
    'root_template' => 'one_column',
    'meta_keywords' => 'zip, zip payment',
    'meta_description' => 'Create your account in moments and select Zip at checkout',
    'identifier' => Zip_Payment_Model_Config::LANDING_PAGE_URL_IDENTIFIER,
    'content_heading' => 'About Zip Payment',
    'stores' => array(0),//available for all store views
    'layout_update_xml' => '',
    'content' => $content
);

Mage::getModel('cms/page')->setData($landingPageData)->save();