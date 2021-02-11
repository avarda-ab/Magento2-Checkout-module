# Avarda Checkout

The module implements two major features to run a Magento 2 single page checkout for a smooth 
flow and integration with Avarda's Check-out 2 API.

## Backend API

The implementation goal of the backend API is to provide an API as a proxy to communicate with 
Avarda. The purpose of using a proxy is to have control of all incoming and outgoing requests with Avarda on the server level. The backend utilizes the standard Magento 2 cart API, with added resources for performing Avarda-specific actions.

## Module Installation

1. `composer require avarda/checkout`  
2. `bin/magento module:enable Avarda_Checkout`  
3. `bin/magento setup:upgrade`

Or 

1. Download the release zip
2. Unzip and upload content to `app/code/Avarda/Checkout/`
3. `bin/magento module:enable Avarda_Checkout`
4. `bin/magento setup:upgrade`

# Known Issues

- Selecting a region is not supported.
- Finland is the only supported country (hardcoded value).
- No return is made in Avarda when a creditmemo is done for order.
    - Creditmemo's must be done online through an invoice 
      or if no invoice yet cancel order will make refund
- inventory is not reserved before payment, which can cause issues when backorders are not enabled for low stock items

## Found and issue in code?
- Please create issue to github

## Found other issues?
- Please contact avarda support
