<?php

namespace Obrainwave\Paygate\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PaymentForm extends Component
{
    public $providers;
    public $currencies;
    public $paymentMethods;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->providers = config('paygate.supported_currencies', []);
        $this->currencies = config('paygate.supported_currencies', []);
        $this->paymentMethods = config('paygate.payment_methods', []);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('paygate::components.payment-form');
    }
}
