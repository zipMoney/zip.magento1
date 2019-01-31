# Zip Payment - Magento 1 extension v2

Here is the second version of Magento 1 extension which has been fully refactored.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

What things you need to install the software and how to install them

- PHP <http://php.net/manual/en/install.php>
- PHP Composer <https://getcomposer.org/doc/00-intro.md>

## Commands

- Build package

``` shell
composer run-script build
```

## Installation

### Install via FTP

- Download the latest package release
- Extract the contents of the package to your computer
- Grab all code from `\src` folder and copy into corresponding folders in Magento 1 root directory

### Install via Magento Connect Manager

- Download the latest package release
- Navigate to `System > Magento Conenct > Magento Connect Manager`
- Go to `Direct package file upload` section and Upload the package
- Press 'Install' button to install this extension

### To install via Module Manager (modman)

- modman update

## Configuration

### Payment Section

- Open the Magento Admin
- Navigate to `Systems -> Configuration` and then locate the `Payment Methods` section in the left menu to access the `Zip Payment` method

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