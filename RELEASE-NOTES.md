# Release Notes

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## **2.2.1** - December 23, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.2.1.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.2.1.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.2.1.bz2)

### Fix - Order payload for discount, fee and pickup

- Fix decimal comparison logic for discount and fee in order items
- Set pickup as false if shipping amount is more than 0

### Engineering - Merge master into develop after deployment

- Add script to Merge master into develop after deployment
- Push code to the Github after deployment

## **2.2.0** - October 9, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.2.0.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.2.0.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.2.0.bz2)

### Fix - Specific countries on checkout page

- Fix issue with specific countries on checkout page. Zip Payment option was still appearing on checkout page even the billing country is not been supported yet.

## **2.1.4** - October 8, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.4.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.4.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.4.bz2)

### Fix - Unclosed HTML tag in payment form template

- Fix unclosed HTML tag in payment form

### Engineering - Command to generate deployment package

- Add deployment package generation task: `composer run-script deploy:package`

## **2.1.3** - September 2, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.3.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.3.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.3.bz2)

### Fix - Issue with Order Cancel and Void from Admin

- Fix order Cancel and Void functions from Magento Admin

## **2.1.2** - August 22, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.2.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.2.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.2.bz2)

### Fix - Issue with Lightbox mode

- Fix 'The checkout Id does not exist' issue for 'lightbox' mode

### Engineering - Hotfix Pull Request

- Support Hotfix Pull Request in Bitbucket Pipeline
- Update Pipeline with definitions 
- Add Random number for generating Order Number Prefix for Docker build

## **2.1.1** - August 12, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.1.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.1.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.1.bz2)

### Fix - Checkout detection for One Page checkout

- Fix checkout detection for default one page checkout
- Fix syntax error in install script
- Add function to detect whether current page is checkout page

## **2.1.0** - July 29, 2019

> Download [Source code (zip)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.0.zip) | [Source code (gz)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.0.gz) | [Source code (bz2)](https://bitbucket.org/zipmoney-platform/zip.magento1/get/v2.1.0.bz2)

### Core - Coding Standards

- Apply latest PHP and Magento coding standards
- Improve Health check for SSL settings
- Provide configuration fields to define checkout type and checkout path
- Add product type into payment information for order
- Improve UI for checkout overlay and spinner
- Add country and currency supports for New Zealand
- Add Cancel function for Authorized Charge
- Add support for Click & Collect shipping method

### Fix - Checkout Redirect for One Step checkout

- Checkout Redirect for One Step checkout
- Composer needs a vendor/package name
- Using secure https url in secure website for checkout resources
- Only accept product SKU with maximum 49 length
- Only accept phone number with maximum 15 length
- Fix Void function for Authorized Charge

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
  