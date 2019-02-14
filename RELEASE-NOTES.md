# Release Notes

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2.0.0 (February 14, 2019)

### Core - Refactor the whole plugin

- Refactored the whole plugin to improve payment process and fix all fundamental issues
- Admin configurations been rebuilt, including new structured configuration panel, admin wizard and admin notifications
- Able to handle store credit, discount & loyalty programs
- Support redirect (by default) and lightbox to process checkout
- Include PHP SDK in the package, do not need composer to install it
- Handle referred applications
- Support in-plugin health check function

### Fix

- Issues related to the event of a checkout error
- Stop the plugin grabbing customer DOB if it exists
- Phone number validation issue
