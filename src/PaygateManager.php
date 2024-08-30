<?php

namespace Obrainwave\Paygate;

use Obrainwave\Paygate\Traits\InitiateTrait;
use Obrainwave\Paygate\Traits\VerifyTrait;

class PaygateManager
{
    use InitiateTrait, VerifyTrait;

    public function initiatePayment($data)
    {
        $payment = $this->initiate($data);

        return $payment;
    }

    public function verifyPayment($data)
    {
        $payment = $this->verify($data);

        return $payment;
    }
}