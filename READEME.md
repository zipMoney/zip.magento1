# Zip Payment - Magento 1 extension v2

Here is the second version of Magento 1 extension which has been fully refactored.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

What things you need to install the software and how to install them

- PHP <http://php.net/manual/en/install.php>
- PHP Composer <https://getcomposer.org/doc/00-intro.md>

## Commands

### Deploy Command

- Build deployment package

``` shell
composer run-script deploy:package
```

## Installation

### Install via FTP

- Download the latest release package
- Extract the contents of the package to your computer
- Grab all code and copy into corresponding folders in Magento 1 root directory

### Install via Magento Connect Manager

- Download the latest package release
- Navigate to `System > Magento Conenct > Magento Connect Manager`
- Go to `Direct package file upload` section and Upload the package
- Press 'Install' button to install this extension

### Install via Module Manager (modman)

- modman update

## Configuration

### Payment Section

- Open the Magento Admin
- Navigate to `Systems -> Configuration` and then locate the `Payment Methods` section in the left menu to access the `Zip Payment - Own it now, pay later` method
- Click `Configure` button to open configuration panel

### Status

- *Version*: Show current version for this extension
- *Health Check*: List all errors and warnings. It's not been allowed to enable the extension if there is any error found in health check.

### Settings

- Set Enable to `Yes` to enable this payment solution
- Choose `environment` and press `Find your <environment> keys` button to log into merchant dashboard and grab private key & public key
- Enter the `Private Key` and `Public Key` in the following fields

#### Checkout

- *Title*: the label will be used as payment option on checkout page
- *Payment Action*: define how payment will be handled, immediate capture or authorize only
- *Display Mode*: different display mode on checkout page to handle Zip confirmation page

#### Referred Appication

- *Order Handling*: whether new order will be created for referred application
- *New Order Status*: define the order status for referred application

#### Country and Currency

This section is used to identify configuration of country and currency

#### Debug Mode

To enable debug mode and define level of log for debugging

#### Admin Notification

Set `Yes` to accept marketing news and notifications

### Widgets and Banners

- Scroll down and expand each page section
- Expand each widget section and set Enable to `Yes` to enable widget
- Type `Element selector` to define the location where this widget should put in

## Copyright

    Copyright 2019 Zip Co

    Licensed under the The MIT License (MIT) (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

        https://opensource.org/licenses/MIT

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.