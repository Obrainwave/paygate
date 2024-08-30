<?php
namespace Obrainwave\Paygate\Traits;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait VerifyTrait 
{
    public function verify($data)
    {
        $data = toObject($data);
        if(!validateVerifyParam($data)->status)
        {
            return validateVerifyParam($data)->message;
        }

        switch($data->provider)
        {
            case 'paystack':
                $paystack_url = config('paygate.paystack.base_url');
                $paystack_token = $data->provider_token;

                $url = "{$paystack_url}/transaction/verify/{$data->reference}";
                $headers = [
                    'Content-Type' => 'application/json',
                ];

                $response = Http::withToken($paystack_token)
                ->withOptions([
                    'headers' => $headers,
                ])->get($url);
                    \Log::info($response);

                if($response->status() == 200)
                {
                    if(isset($response['status']) && $response['status'] == true)
                    {
                        $payment = $this->successVerify($response, $data->provider);
                    }else{
                        $payment = $this->failVerify($response);
                    }
                }else{
                    $payment = failMsg($data->provider, $response);
                }
                break;

            case 'gtpay':
                $gtpay_url = config('paygate.gtpay.base_url');
                $gtpay_token = $data->provider_token;
                
                $url = "{$gtpay_url}/transaction/verify/{$data->reference}";
                $headers = [
                    'Content-Type' => 'application/json',
                ];

                $response = Http::withToken($gtpay_token)
                ->withOptions([
                    'headers' => $headers,
                ])->get($url);
                    \Log::info($response);
                if($response->status() == 200)
                {
                    if(isset($response['status']) && $response['status'] == true)
                    {
                        $payment = $this->successVerify($response, $data->provider);
                    }else{
                        $payment = $this->failVerify($response);
                    }
                }else{
                    $payment = failMsg($data->provider, $response);
                }
                break;

            case 'flutterwave':
                $flutterwave_url = config('paygate.flutterwave.base_url');
                $flutterwave_token = $data->provider_token;
                // $url = "{$flutterwave_url}/v3/transactions/{$order->transaction->transaction_id}/verify";
                $url = "{$flutterwave_url}/v3/transactions/verify_by_reference?tx_ref={$data->reference}";
                $headers = [
                    'Content-Type' => 'application/json',
                ];
                $response = Http::withToken($flutterwave_token)
                ->withOptions([
                    'headers' => $headers,
                ])->get($url);
                    \Log::info($response);
                if($response->status() == 200)
                {
                    if(isset($response['status']) && $response['status'] == 'success')
                    {
                        $payment = $this->successVerify($response, $data->provider);
                    }else{
                        $payment = $this->failVerify($response);
                    }
                }
                break;

            case 'monnify':
                $monnify_url = config('paygate.monnify.base_url');
                $monnify_token = $data->provider_token;
                $exp = explode('|', $data->reference);
                $link = isset($exp[0]) && $exp[0] == 'MNFY' ? 'transactionReference' : 'paymentReference';
                $url = "{$monnify_url}/api/v2/merchant/transactions/query?{$link}={$data->reference}";
                $headers = [
                    'Content-Type' => 'application/json',
                ];
                $response = Http::withToken($monnify_token)
                ->withOptions([
                    'headers' => $headers,
                ])->get($url);
                    \Log::info($response);
                if($response->status() == 200)
                {
                    if(isset($response['requestSuccessful']) && $response['requestSuccessful'] == true)
                    {
                        $payment = $this->successVerify($response, $data->provider);
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



    public function successVerify($response, $provider)
    {
        switch($provider)
        {
            case 'paystack':
                $data = isset($response['data']) ? $response['data'] : null;
                break;

            case 'gtpay':
                $data = isset($response['data']) ? $response['data'] : null;
                break;
            
            case 'flutterwave': 
                $data = isset($response['data']) ? $response['data'] : null;
                break;

            case 'monnify':
                $data = isset($response['responseBody']) ? $response['responseBody'] : null;
                break;
        }
        $res = [
            'errors' => false,
            'message' => 'Payment fetched successfully with '.$provider,
            'data' => $data, 
            'provider' => $provider     
        ];
        return toObject($res);
    }

}