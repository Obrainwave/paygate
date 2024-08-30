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

### Initiate Payment/Transaction
You can load Paygate instance using **`use \Obrainwave\Paygate\Facades\Paygate`** or just use the facade **`use Paygate`**.
##### Request Sample
```php
use Paygate;

$payload = array(
  'provider' => 'paystack',
  'provider_token' => 'PAYSTACK_SECRET_KEY', // Make sure you don't expose this in your code
  'amount' => 250,
  'email' => 'ola@dev.com',
  'reference' => 'T2CZ143DUMG',
  'redirect_url' => 'https://mydomain.com/verify-payment',
  'name' => 'Akeem Salau', 
  'contract_code' => '32904826734',
  'payment_methods' => ["card", "bank", "ussd", "qr", "mobile_money", "bank_transfer", "eft"]
  'pass_charge' => False
  'title' => "Ola's Store"
  'logo' => 'https://mydomain.com/logo.png'
  'phone_number' => '08022999871'
 );

$payment = Paygate::initiatePayment($payload);
```

#### Request Fields
The table below shows and explains the field features. <br/>Note the **Mandatory(M)**, **Optional(O)**, and **Not Applicable(N/A)** used in the table.

| Field Name | Type | Paystack | GTPay | Flutterwave | Monnify | Description |
| ----- | ----- | ----- | ----- | ----- | ----- | ----- |
| `provider` | string | M  | M | M | M | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**. |
| `provider_token` | string | M  | M | M | M | This is the payment gateway `access_token` or `API Secret Key`. <br/> For **Monnify** only, you your `API Key` and `Secret Key` should be in `ApiKey:SecretKey` format as `provider_token` |
| `amount` | float  | M  | M | M | M | This is the amount to be charged for the transaction or the amount you are debiting customer. |
| `email` | string | M  | M | M | M | Customer's email address |
| `reference` | string | M | M | M | M | Your unique generated reference |
| `redirect_url` | url | O  | O | O | O | Fully qualified url, e.g. https://example.com/ . <br/>Use this to override the callback url provided on the dashboard for this transaction |
| `name` | string | O  | O | O | M | Customer's name |
| `contract_code` | string | N/A  | N/A | N/A | M | Customer's email address |
| `payment_methods` | array | O  | O | O | O | An array of payment methods or channels to control what channels you want to make available to the user to make a payment with. <br/>E.g available channels for `paystack` include: ["card", "bank", "ussd", "qr", "mobile_money", "bank_transfer", "left"]. <br/>Check other payment gateways documentation for their available payment methods or channels.  | 
| `pass_charge` | boolean | N/A  | O | N/A | N/A | This is only applicable to **gtpay**.<br/> It takes two possible values: True or False.<br/>It is set to False by default. When set to True, the charges on the transaction is computed and passed on to the customer(payer).<br/>But when set to False, the charge is passed to the merchant and will be deducted from the amount to be settled to the merchant. | 
| `logo` | url | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>The Merchant's logo URL. |
| `title` | url | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>The name to display on the checkout page. |
| `phone_number` | string | N/A  | N/A | O | N/A | This is only applicable to **flutterwave**.<br/>This is the phone number linked to the customer's bank account or mobile money account |


#### Response Sample
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
Note that the table below shows and explains the most important fields to complete the payment or transaction.

| Field Name | Type | Description |
| ----- | ----- | ----- |
| `errors` | boolean | Can only be **true** or **false**. The request was successful if **true** and **false** if the request was not successful |
| `message` | string | Short description of the request  |
| `data` | object | This contains all the parameters you need to complete the payment or transaction |
| `data->checkout_url` | url | This is the checkout url from the payment gateway for the customer to complete the payment. You can redirect this url to give customer interface. |
| `data->reference` | string | Merchant's Unique reference for the transaction. The unique generated reference you send via request to payment gateway. |
| `data->access_code` | string | Unique reference generated for the transaction by payment gateway. |
| `provider` | string | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**.   |



### Verify Payment/Transaction
You can load Paygate instance using **`use \Obrainwave\Paygate\Facades\Paygate`** or just use the facade **`use Paygate`**.
#### Request Sample
```php
use Paygate;

$payload = array(
  'provider' => 'paystack',
  'provider_token' => 'PAYSTACK_SECRET_KEY', // Make sure you don't expose this in your code
  'reference' => 'T2CZ143DUMG',
  
 );

$payment = Paygate::verifyPayment($payload);
```
#### Request Fields
The table below shows and explains the field features. <br/>Note the **Mandatory(M)**, **Optional(O)**, and **Not Applicable(N/A)** used in the table.

| Field Name | Type | Paystack | GTPay | Flutterwave | Monnify | Description |
| ----- | ----- | ----- | ----- | ----- | ----- | ----- |
| `provider` | string | M  | M | M | M | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**. |
| `provider_token` | string | M  | M | M | M | This is the payment gateway `access_token` or `API Secret Key`. <br/> For **Monnify** only, you your `API Key` and `Secret Key` should be in `ApiKey:SecretKey` format as `provider_token` |
| `reference` | string | M | M | M | M | Your unique generated reference sent to payment gateway. It should also be returned via payment initiation response |


#### Response Sample
If successful, you will receive a response that looks like the below sample response
```
{
  "errors": false
  "message": "Payment fetched successfully with gtpay"
  "provider": "gtpay"
  "status": "successful"
  "amount": 230
  "charged_amount": 232.3
  "reference": "9412041935"
  "payment_method": "card"
  "data": {
    ...
  }
}
```

#### Response Fields
Note that the table below shows and explains the most important fields that decide the payment or transaction status.

| Field Name | Type | Description |
| ----- | ----- | ----- |
| `errors` | boolean | Can only be **true** or **false**. The request was successful if **true** and **false** if the request was not successful |
| `message` | string | Short description of the request  |
| `provider` | string | This is the payment gateway name.<br/> For now can only be **paystack**, **gtpay**, **flutterwave** and **monnify**.   |
| `status` | string | Can only be **successful** or **failed**. The payment was completed and successful if **successful** and **failed** if the payment was not successful or not completed.<br/> If you want to dig more about the `status`, check for payment gateway transaction status in `data` field. <br/> `status` for **paystack**, `transaction_status` for **gtpay**, `status` for **flutterwave**, and `paymentStatus` for **monnify**.  |
| `data` | object | This contains all the parameters you need to play with the payment or transaction verification |




## Conclusion
Presently only local payment gateways work. International payment gateways(such as Stripe, Paypal, etc) will be added. <br/>For this package only initiates and verifies transactions at minimum version. More features will be added in subsequent versions. **Please watchout!!!**



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
