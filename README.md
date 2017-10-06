# n98-magerun dev:env:set command
Updates the Magento config with values defined in n98-magerun.yaml.

## Installation

Clone with GitHub into the n98-magerun modules directory:
```bash
cd ~/.n98-magerun/modules/
git clone ...
```

n98-magerun should pick up the commands automatically without further intervention.

## Compatibility
The command has been tested with all versions of Magento 1 and Magento 2 up to 2.1.9.

It has not been tested on Magento 2.2 (yet). 

## Command

### dev:env:set
Update a Magento environment to use the specified settings. Run this command after setting up a new Magento 1.x/2.1.x instance to set default config values.

#### Usage

```bash
$ n98-magerun.phar dev:env:set --help

Usage:
  dev:env:set [<env>]

Arguments:
  env                        An environment to configure.
```

#### Examples

```bash
# Updates the Magento environment to the settings specified in the 'localhost' key in the YAML
$ n98-magerun.phar dev:env:set localhost

# Choose an environment to update from those in the n98-magerun.yaml
$ n98-magerun.phar dev:env:set
```

Configuration scopes and values are set in the n98-magerun.yaml or n98-magerun2.yaml file (based on which version of n98-magerun you're running).

If no environment code (e.g. 'localhost', 'test', 'staging') is specified on the command line, the command reads the YAML and allows the user to choose an environment.

#### Configuration

Add the following to your n98-magerun.yaml/n98-magerun2.yaml.

Pro tip: If you're not sure of the `scope`, `scope ID`, `config path` or possible `values`, then try saving the value in the admin, then find it in the `core_config_data` table in the database and just copy them below.

```yaml
commands:
  ProjectEight\Magento\Command\Developer\Environment\SetCommand:
    environments:
      localhost:    # Define a new environment called 'localhost'
        config:     
          default:  # Configuration scope (default, websites, stores)
            0:      # Configuration scope ID 
              general/country/default: GB
              general/store_information/merchant_country: GB
              design/head/demonotice: 1 # Example of setting the value for a Yes/No config value
              trans_email/ident_general/email: projecteight@example.com
              trans_email/ident_sales/email: projecteight@example.com
              trans_email/ident_support/email: projecteight@example.com
              trans_email/ident_custom1/email: projecteight@example.com
              trans_email/ident_custom2/email: projecteight@example.com
              contacts/email/recipient_email: projecteight@example.com
              sitemap/generate/error_email: projecteight@example.com
              customer/password/require_admin_user_to_change_user_password: 0
              tax/defaults/country: GB
              tax/defaults/postcode: "YO24 1BF"
              shipping/origin/country_id: GB
              shipping/origin/region_id: North Yorkshire
              shipping/origin/postcode: "YO24 1BF"
#              Shipping methods:
              carriers/dhlint/active: 0
              carriers/dhl/active: 0
              carriers/fedex/active: 0
              carriers/usps/active: 0
              carriers/ups/active: 0
              google/analytics/account: UA-123456-AB
              payment/account/merchant_country: GB
              # PayPal Express Checkout:
#              payment/express_checkout_required_express_checkout/business_account
#              payment/express_checkout_required_express_checkout/api_authentication
#              payment/express_checkout_required_express_checkout/api_username
#              payment/express_checkout_required_express_checkout/api_password
#              payment/express_checkout_required_express_checkout/api_signature
#              payment/express_checkout_required_express_checkout/sandbox_flag
#              payment/express_checkout_required/enable_express_checkout: 0
              payment/settings_ec/payment_action: Sale
              payment/settings_ec_advanced/debug: 1
#              Other payment methods:
              payment/checkmo/active: 1
              payment/checkmo/specificcountry: GB,DE # Example of defining the value for a multi-select config value
              admin/security/extensions_compatibility_mode: Disabled
              system/smtp/disable: 1
              dev/log/active: 1
              dev/restrict/allow_ips: 127.0.0.1
      staging:
        config:
          websites:
            1:
              general/country/default: FR
          stores:
            3:
              general/country/default: DE
