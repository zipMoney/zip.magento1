<?php

// allow zip payment widget in block permission
$widgetBlockName = 'zip_payment/widget';
Mage::getModel('admin/block')->load($widgetBlockName,'block_name')
->setData('block_name', $widgetBlockName)
->setData('is_allowed', 1)
->save();