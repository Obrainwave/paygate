<?php
namespace Obrainwave\Paygate\Traits;

use Illuminate\Support\Facades\Http;

trait InitiateTrait 
{
    public function initiate($data) : object
    {
        $data = toObject($data);
        
        if(!validateParam($data)->status)
        {
            return validateParam($data)->message;
        }

        switch($data->provider)
        {
            case 'paystack':
                $paystack_url = config('paygate.paystack.base_url');
                $paystack_token = $data->provider_token;

                $url = "{$paystack_url}/transaction/initialize";
                $headers = [
                    'Content-Type' => 'application/json',
                ];

                $payload = [
                    'email' => $data->email,
                    'amount' => $data->amount * 100,
                    'currency' => isset($data->currency) ? $data->currency : 'NGN',
                    'reference' => isset($data->reference) ? $data->reference : null,
                    'channels' => isset($data->payment_methods) ? $data->payment_methods : null,
                    'callback_url' => isset($data->redirect_url) ? $data->redirect_url : null,
                ];

                $response = Http::withToken($paystack_token)
                ->withOptions([
                    'headers' => $headers,
                ])->post($url, $payload);
                    
                if($response->status() == 200)
                {
                    if(isset($response['status']) && $response['status'] == true)
                    {
                        $payment = $this->successInitiate($response, $data->provider);
                    }else{
                        $payment = failMsg($data->provider, $response);
                    }
                }else{
                    $payment = failMsg($data->provider, $response);
                }
                break;

            case 'gtpay':
                $gtpay_url = config('paygate.gtpay.base_url');
                $gtpay_token = $data->provider_token;

                $url = "{$gtpay_url}/transaction/initiate";
                $headers = [
                    'Content-Type' => 'application/json',
                ];

                $payload = [
                    'transaction_ref' => isset($data->reference) ? $data->reference : null,
                    'customer_name' => isset($data->name) ? $data->name : null,
                    'email' => $data->email,
                    'amount' => $data->amount * 100,
                    'currency' => isset($data->currency) ? $data->currency : 'NGN',
                    'initiate_type' => isset($data->initiate_type) ? $data->initiate_type : 'inline',
                    'callback_url' => isset($data->redirect_url) ? $data->redirect_url : null,
                    'payment_channels' => isset($data->payment_methods) ? $data->payment_methods : [],
                    'pass_charge' => isset($data->pass_charge) ? $data->pass_charge : false,
                ];

                $response = Http::withToken($gtpay_token)
                ->withOptions([
                    'headers' => $headers,
                ])->post($url, $payload);
                    
                if($response->status() == 200)
                {
                    if(isset($response['status']) && $response['status'] == true)
                    {
                        $payment = $this->successInitiate($response, $data->provider);
                    }else{
                        $payment = failMsg($data->provider, $response);
                    }
                    
                }else{
                    $payment = failMsg($data->provider, $response);
                }
                break;
            
            case 'flutterwave':
                $flutterwave_url = config('paygate.flutterwave.base_url');
                $flutterwave_token = $data->provider_token;

                $url = "{$flutterwave_url}/v3/payments";
                $headers = [
                    'Content-Type' => 'application/json',
                ];

                $payload = [
                    'tx_ref' => $data->reference,
                    'amount' => $data->amount,
                    'currency' => isset($data->currency) ? $data->currency : 'NGN',
                    'redirect_url' => isset($data->redirect_url) ? $data->redirect_url : null,
                    'customer' => [
                        'email' => $data->email,
                        'phone_number' => isset($data->phone_number) ? $data->phone_number : null,
                        'name' => isset($data->name) ? $data->name : null,
                    ],
                    'customizations' => [
                        'title' => isset($data->title) ? $data->title : null,
                        'logo' => isset($data->logo) ? $data->logo : null,
                    ]
                ];

                $response = Http::withToken($flutterwave_token)
                ->withOptions([
                    'headers' => $headers,
                ])->post($url, $payload);
                   
                if($response->status() == 200)
                {
                    if(isset($response['status']) && $response['status'] == 'success')
                    {
                        $payment = $this->successInitiate($response, $data->provider);
                    }else{
                        $payment = failMsg($data->provider, $response, $data->reference);
                    }
                    
                }else{
                    $payment = failMsg($data->provider, $response);
                }
                break;

            case 'monnify':
                $monnify_url = config('paygate.monnify.base_url');
                $token = $this->generateMonnifyToken($data->provider_token);

                if(isset($token->status) && $token->status == true)
                {
                    $monnify_token = $token->token;
                }else{
                    $res = [
                        'error' => 'Unauthorized',
                        'error_description' => 'Unable to generate monnify access token. Please make sure you are using correct API KEY and SECRET KEY from your monnify dasboard'
                    ];

                    return failMsg($data->provider, $res);
                }

                $url = "{$monnify_url}/api/v1/merchant/transactions/init-transaction";
                $headers = [
                    'Content-Type' => 'application/json',
                ];

                $payload = [
                    'paymentReference' => $data->reference,
                    'customerName' => $data->name,
                    'customerEmail' => $data->email,
                    'amount' => $data->amount,
                    'currencyCode' => isset($data->currency) ? $data->currency : 'NGN',
                    'contractCode' => $data->contract_code,
                    'paymentMethods' => isset($data->payment_methods) ? $data->payment_methods : null,
                    'redirectUrl' => isset($data->redirect_url) ? $data->redirect_url : null,
                ];

                $response = Http::withToken($monnify_token)
                ->withOptions([
                    'headers' => $headers,
                ])->post($url, $payload);
                    
                if($response->status() == 200)
                {
                    if(isset($response['requestSuccessful']) && $response['requestSuccessful'] == true)
                    {
                        $payment = $this->successInitiate($response, $data->provider);
                    }else{
                        $payment = failMsg($data->provider, $response);
                    }
                    
                }else{
                    $payment = failMsg($data->provider, $response);
                }
                break;
        }

        return $payment;
    }



    public function successInitiate($response, $provider, $reference=null) : object
    {
        switch($provider)
        {
            case 'paystack':
                $checkout_url = isset($response['data']['authorization_url']) ? $response['data']['authorization_url'] : null;
                $ref = isset($response['data']['reference']) ? $response['data']['reference'] : null;
                $access_code = isset($response['data']['access_code']) ? $response['data']['access_code'] : null;
                break;

            case 'gtpay':
                $checkout_url = isset($response['data']['checkout_url']) ? $response['data']['checkout_url'] : null;
                $ref = isset($response['data']['transaction_ref']) ? $response['data']['transaction_ref'] : null;
                $access_code = isset($response['data']['transaction_ref']) ? $response['data']['transaction_ref'] : null;
                break;
            
            case 'flutterwave': 
                $checkout_url = isset($response['data']['link']) ? $response['data']['link'] : null;
                $ref = $reference;
                $exp = explode('/', $checkout_url);
                $access_code = end($exp);
                break;

            case 'monnify':
                $checkout_url = isset($response['checkoutUrl']) ? $response['checkoutUrl'] : null;
                $ref = isset($response['responseBody']['paymentReference']) ? $response['responseBody']['paymentReference'] : null;
                $access_code = isset($response['responseBody']['transactionReference']) ? $response['responseBody']['transactionReference'] : null;
                break;

        }
        $res = [
            'errors' => false,
            'message' => 'Payment initiated successfully with '.$provider,
            'data' => [
                'checkout_url' => $checkout_url,
                'reference' => $ref,
                'access_code' => $access_code,   
                'provider' => $provider 
            ]        
        ];

        return toObject($res);
    }


    private function generateMonnifyToken($keys)
    {
        $key = base64_encode($keys);
        $monnify_url = config('paygate.monnify.base_url');
        $monnify_keys = $keys;

        $url = "{$monnify_url}/api/v1/auth/login";
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => "Basic " . base64_encode($monnify_keys)
        ];

        $payload = [];

        $response = Http::withOptions([
            'headers' => $headers,
        ])->post($url, $payload);
           
        if($response->status() == 200)
        {
            if(isset($response['requestSuccessful']) && $response['requestSuccessful'] == true)
            {
                $res = [
                    'status' => true,
                    'token' => $response['responseBody']['accessToken'],
                ];
            }else{
                $res = [
                    'status' => false,
                    'token' => null
                ];
            }
            
        }else{
            $res = [
                'status' => false,
                'token' => null
            ];
        }

        return toObject($res);

    }
}