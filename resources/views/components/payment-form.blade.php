<div class="paygate-payment-form">
    <form id="paygate-payment-form" method="POST" action="{{ route('paygate.initiate') }}">
        @csrf

        <div class="form-group">
            <label for="provider">Payment Provider</label>
            <select name="provider" id="provider" class="form-control" required>
                <option value="">Select Provider</option>
                <option value="paystack" {{ old('provider') == 'paystack' ? 'selected' : '' }}>Paystack</option>
                <option value="gtpay" {{ old('provider') == 'gtpay' ? 'selected' : '' }}>GTPay</option>
                <option value="flutterwave" {{ old('provider') == 'flutterwave' ? 'selected' : '' }}>Flutterwave
                </option>
                <option value="monnify" {{ old('provider') == 'monnify' ? 'selected' : '' }}>Monnify</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount') }}"
                step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="currency">Currency</label>
            <select name="currency" id="currency" class="form-control" required>
                <option value="NGN" {{ old('currency') == 'NGN' ? 'selected' : '' }}>NGN - Nigerian Naira</option>
                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}"
                required>
        </div>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" name="phone_number" id="phone" class="form-control"
                value="{{ old('phone_number') }}">
        </div>

        <div class="form-group">
            <label for="reference">Reference (Optional)</label>
            <input type="text" name="reference" id="reference" class="form-control"
                value="{{ old('reference', 'TXN_' . time()) }}">
        </div>

        <div class="form-group">
            <label for="redirect_url">Redirect URL</label>
            <input type="url" name="redirect_url" id="redirect_url" class="form-control"
                value="{{ old('redirect_url', url('/payment/callback')) }}">
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                Initiate Payment
            </button>
        </div>
    </form>

    <script>
        document.getElementById('paygate-payment-form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
        });
    </script>
</div>

<style>
    .paygate-payment-form {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
    }

    .paygate-payment-form .form-group {
        margin-bottom: 15px;
    }

    .paygate-payment-form .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }

    .paygate-payment-form .btn {
        width: 100%;
        padding: 12px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .paygate-payment-form .btn:hover {
        background: #0056b3;
    }

    .paygate-payment-form .btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }
</style>
