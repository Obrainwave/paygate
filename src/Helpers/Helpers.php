<?php
function toObject($data) : object
{
    $res = json_decode(json_encode($data), FALSE);

    return $res;
}


function failMsg($provider, $response=null) : object
{
    switch($provider)
    {
        case 'paystack':
            $error = [
                'errors' => true,
                'message' => isset($response['message']) ? $response['message'] : 'Payment failed to initiate successfully without a specific error',
                'description' => isset($response['meta']) && 
                                isset($response['meta']['nextStep']) ? 
                                $response['meta']['nextStep'] : 
                                'Make sure you are passing correct parameters in your payload and also make sure your Paystack API Key is valid'
            ];
            break;

        case 'gtpay':
            $error = [
                'errors' => true,
                'message' => isset($response['message']) ? $response['message'] : 'Payment failed to initiate successfully without a specific error',
                'description' => isset($response['data']) && $response['data'] != [] && $response['data'] != null ? 
                                $response['data'] : 
                                'Make sure you are passing correct parameters in your payload and also make sure your Gtpay API Key is valid'
            ];
            break;

        case 'flutterwave':
            $error = [
                'errors' => true,
                'message' => isset($response['message']) ? $response['message'] : 'Payment failed to initiate successfully without a specific error',
                'description' => isset($response['errors'])? 
                                $response['errors'] : 
                                'Make sure you are passing correct parameters in your payload and also make sure your Flutterwave API Key is valid'
            ];
            break;

        case 'monnify':
            if(isset($response['error']))
            {
                $msg = $response['error'];
            }elseif(isset($response['responseMessage']))
            {
                $msg = $response['responseMessage'];
            }else{
                $msg = 'Payment failed to initiate successfully without a specific error';
            }

            if(isset($response['error_description']))
            {
                $desc = $response['error_description'];
            }elseif(isset($response['responseMessage']))
            {
                $desc = $response['responseMessage'];
            }else{
                $desc = 'Make sure you are passing correct parameters in your payload and also make sure your Gtpay API Key is valid';
            }
            $error = [
                'errors' => true,
                'message' => $msg,
                'description' => $desc
            ];
            break;
        default:
            $error = [
                'errors' => true,
                'message' => 'Invalid parameters! You must provide a valid provider',
                'description' => 'You are missing an important parameter. Please provide either paystack, gtpay, flutterwave or monnify'
            ];
    }

    return toObject($error);
}


function providerErr() : object
{
    $error = [
        'errors' => true,
        'message' => 'Invalid parameters! You must provide a valid provider',
        'description' => 'You are missing an important parameter. Please provide either paystack, gtpay, flutterwave or monnify'
    ];

    return toObject($error);
}

function fieldErr($field) : object
{
    $error = [
        'errors' => true,
        'message' => 'Invalid parameters! You must provide '.$field.' parameter',
        'description' => 'You are missing an important parameter. Please provide '.$field.' in your payload'
    ];

    return toObject($error);
}

function validateParam($data) : object
{
    if(!isset($data->provider))
    {
        $res = [
            'status' => false,
            'message' => providerErr()
        ];

        return toObject($res);
    }

    if(!isset($data->email) || $data->email == null)
    {
        $res = [
            'status' => false,
            'message' => fieldErr('email')
        ];

        return toObject($res);
    }

    if(!isset($data->amount) || $data->amount == null)
    {
        $res = [
            'status' => false,
            'message' => fieldErr('amount')
        ];

        return toObject($res);
    }

    if(!isset($data->provider_token) || $data->provider_token == null)
    {
        $res = [
            'status' => false,
            'message' => fieldErr('provider_token')
        ];

        return toObject($res);
    }
   
    switch($data->provider)
    {
        case 'paystack':
            $res = [
                'status' => true
            ];
            break;

        case 'gtpay':
            $res = [
                'status' => true
            ];
            break;

        case 'flutterwave':
            if(!isset($data->redirect_url) || $data->redirect_url == null)
            {
                $res = [
                    'status' => false,
                    'message' => fieldErr('redirect_url')
                ];

                return toObject($res);
            }
            if(!isset($data->custom_ref) || $data->custom_ref == null)
            {
                $res = [
                    'status' => false,
                    'message' => fieldErr('custom_ref')
                ];

                return toObject($res);
            }
            break;

        case 'monnify':
            if(!isset($data->name) || $data->name == null)
            {
                $res = [
                    'status' => false,
                    'message' => fieldErr('name')
                ];

                return toObject($res);
            }
            if(!isset($data->contract_code) || $data->contract_code == null)
            {
                $res = [
                    'status' => false,
                    'message' => fieldErr('contract_code')
                ];

                return toObject($res);
            }
            if(!isset($data->redirect_url) || $data->redirect_url == null)
            {
                $res = [
                    'status' => false,
                    'message' => fieldErr('redirect_url')
                ];

                return toObject($res);
            }
            if(!isset($data->custom_ref) || $data->custom_ref == null)
            {
                $res = [
                    'status' => false,
                    'message' => fieldErr('custom_ref')
                ];

                return toObject($res);
            }
            break;

        default:
            $res = [
                'status' => false,
                'message' => providerErr()
            ];
            
    }
    $res = [
        'status' => true
    ];
    return toObject($res);
}


function validateVerifyParam($data) : object 
{
    if(!isset($data->provider))
    {
        $res = [
            'status' => false,
            'message' => providerErr()
        ];

        return toObject($res);
    }

    if(!isset($data->reference) || $data->reference == null)
    {
        $res = [
            'status' => false,
            'message' => fieldErr('reference')
        ];

        return toObject($res);
    }

    if(!isset($data->provider_token) || $data->provider_token == null)
    {
        $res = [
            'status' => false,
            'message' => fieldErr('provider_token')
        ];

        return toObject($res);
    }

    $res = [
        'status' => true,
    ];

    return toObject($res);
}