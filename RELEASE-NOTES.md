# Release Notes

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## **2.1.0** - July 9, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.0.0.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.0.0.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.0.0.bz2)

### Core - Coding Standards

- Apply latest PHP and Magento coding standards
- Improve Health check for SSL settings
- Provide configuration fields to define checkout type and checkout path
- Add product type into payment information for order
- Improve UI for checkout overlay and spinner
- Add country and currency supports for New Zealand

### Fix - Checkout Redirect for One Step checkout

- Checkout Redirect for One Step checkout
- Composer needs a vendor/package name
- Using secure https url in secure website for checkout resources
- Only accept product SKU with maximum 49 length
- Only accept phone number with maximum 15 length

### Engineering - Create Docker compose for local development

- Add docker support for local development and testing
- Update Bitbucket Pipeline

## **2.0.0** - February 14, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.0.0.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.0.0.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.0.0.bz2)

### Core - Extension Refactoring

- Refactored the whole plugin to improve payment process and fix all fundamental issues
- Admin configurations been rebuilt, including new structured configuration panel, admin wizard and admin notifications
- Support all discount extensions, including gift-card, store credit, reward points and more
- Support handling fee, shipping insurance and other surcharges.
- Support redirect (by default) and lightbox to process checkout
- Include PHP SDK in the package, do not need composer to install it
- Handle referred applications
- Support in-plugin health check function
- Support PHP 5.3 and Magento 1.7

### Fix - Legacy issue fixes

- Issues related to the event of a checkout error
- Stop the plugin grabbing customer DOB if it exists
- Phone number validation issue

## **1.0.6** - October 18, 2018

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.6.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.6.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.6.bz2)

### Engineering - Coding Standard with Codacy

- Add Codacy status in README file
- Apply coding standard to the plugin and fix Bugs
- Add codacy settings

## **1.0.5** - June 18, 2018

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.5.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.5.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.5.bz2)

### Deployment - Update Package XML file

- Update package xml file

## **1.0.4** - June 15, 2018

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.4.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.4.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.4.bz2)

### Core - Configuration update

- Update configuration text and images
- Update payment title
- Update asset URIs

## **1.0.3** - April 30, 2018

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.3.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.3.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.3.bz2)

### Core - Checkout Script

- Replace configuration field from 'Payment Action' to 'Payment Mode'
- Add 'holding' overlay on checkout page
- Update checkout script to handle billing save

## **1.0.2** - February 9, 2018

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.2.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.2.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.2.bz2)

### Core - Order Placement Refactoring

- Refactor the order state validation
- Add the payment info block to display the receipt number in invoices
- Rename the data setup directory
- Update the mageci source repository
- Aitoc Checkout Compatibility

## **1.0.1** - September 26, 2017

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.1.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.1.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.1.bz2)

### Core - Code cleanup

- Code cleanup
- Update Payload

## **1.0.0** - July 26, 2017

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.0.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.0.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v1.0.0.bz2)

### Core - Build Extension

- Build Magento 1 extension
- Add Onestepcheckout Extensions Compatibility
- Handle Zip API calls
- Identify checkout flow
- Implement marketing widget integration
  