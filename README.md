# Documentation, Installation, and Usage Instructions for laravel package(Paygate)

This laravel package was written to seamlessly handle payment gateway(such as paystack, gtpay, flutterwave and monnify) with only one single logic.



## Installation

#### You can install the package via composer:

```bash
composer require obrainwave/paygate
```

#### You can publish the config file with:

```bash
php artisan vendor:publish --tag="paygate-config"
```



## Usage

#### Initiate Payment/Transaction
##### Request Sample
```php
use Paygate;

$payload = array(
  'provider' => 'paystack',
  'provider_token' => 'PAYSTACK_SECRET_KEY', // Make sure you don't expose this in your code
  'amount' => 250,
  'email' => 'ola@dev.com',
  'custom_ref' => 'T2CZ143DUMG',
  'redirect_url' => 'https://mydomain.com/verify-payment',
  'name' => 'Akeem Salau', 
  'contract_code' => '32904826734',
  'payment_methods' => ["card", "bank", "ussd", "qr", "mobile_money", "bank_transfer", "eft"]
  'pass_charge' => False
  'title' => "Ola's Store"
  'logo' => 'https://mydomain.com/logo.png'
  'phone_number' => '08022999871'
 );

$payment = Paygate::initiatePayment($data);
```

#### Request Fields
The table below shows and explains the fields feature. <br/>Note the **Mandatory(M)**, **Optional(O)**, and **Not Applicable(N/A)** used in the table.

| Field Name | Type | Paystack | GTPay | Flutterwave | Monnify | Description |
| ----- | ----- | ----- | ----- | ----- | ----- | ----- |
| `provider` | string | M  | M | M | M | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**. |
| `provider_token` | string | M  | M | M | M | This is the payment gateway `access_token` or `API Secret Key`. <br/> For **Monnify** only, you your `API Key` and `Secret Key` should be in `ApiKey:SecretKey` format as `provider_token` |
| `amount` | float  | M  | M | M | M | This is the amount to be charged for the transaction or the amount you are debiting customer. |
| `email` | string | M  | M | M | M | Customer's email address |
| `custom_ref` | string | M | M | M | M | Your unique generated reference |
| `redirect_url` | url | O  | O | O | O | Fully qualified url, e.g. https://example.com/ . <br/>Use this to override the callback url provided on the dashboard for this transaction |
| `name` | string | O  | O | O | M | Customer's name |
| `contract_code` | string | N/A  | N/A | N/A | M | Customer's email address |
| `payment_methods` | array | O  | O | O | O | An array of payment methods or channels to control what channels you want to make available to the user to make a payment with. <br/>E.g available channels for `paystack` include: ["card", "bank", "ussd", "qr", "mobile_money", "bank_transfer", "left"]. <br/>Check other payment gateways documentation for their available payment methods or channels.  | 
| `pass_charge` | boolean | N/A  | O | N/A | N/A | This is only applicable to **gtpay**.<br/> It takes two possible values: True or False.<br/>It is set to False by default. When set to True, the charges on the transaction is computed and passed on to the customer(payer).<br/>But when set to False, the charge is passed to the merchant and will be deducted from the amount to be settled to the merchant. | 
| `logo` | url | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>The Merchant's logo URL. |
| `title` | url | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>The name to display on the checkout page. |
| `phone_number` | string | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>This is the phone number linked to the customer's bank account or mobile money account |


##### Response Sample
If successful, you will receive a response that looks like the below sample response
```
{
  "errors": false
  "message": "Payment initiated successfully with paystack"
  "data": {
    "checkout_url": "https://checkout.paystack.com/gfe327lipw13uit"
    "reference": "T2CZ143DUMG"
    "access_code": "gfe327lipw13uit"
    "provider": "paystack"
  }
}
```


#### Response Fields
Note that the table below on shows and explains most important fields to complete the payment or transaction.

| Field Name | Type | Description |
| ----- | ----- | ----- |
| `errors` | boolean | Can only be **true** or **false**. The request was successful if **true** and **false** if the request was not successful |
| `message` | string | Short description of the request  |
| `data` | object | This contains all the parameters you need to complete the payment or transaction |
| `data->checkout_url` | url | This is the checkout url from the payment gateway for the customer to complete the payment. You can redirect this url to give customer interface. |



## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.



## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.



## Credits

- [Olaiwola Akeem Salau](https://github.com/Obrainwave)
- [All Contributors](../../contributors)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
