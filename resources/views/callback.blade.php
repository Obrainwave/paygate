<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Callback - Paygate</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .status-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .status-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .status-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: 500;
            color: #333;
        }
        .detail-value {
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0056b3;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($result && !$result->errors)
            @if($result->status === 'successful')
                <div class="status-icon success">✓</div>
                <div class="status-title success">Payment Successful!</div>
                <div class="status-message">
                    Your payment has been processed successfully. You will receive a confirmation email shortly.
                </div>
                
                <div class="payment-details">
                    <div class="detail-row">
                        <span class="detail-label">Reference:</span>
                        <span class="detail-value">{{ $result->reference ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Amount:</span>
                        <span class="detail-value">₦{{ number_format($result->amount ?? 0, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Provider:</span>
                        <span class="detail-value">{{ ucfirst($result->provider ?? 'N/A') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value">{{ ucfirst($result->payment_method ?? 'N/A') }}</span>
                    </div>
                </div>
            @else
                <div class="status-icon error">✗</div>
                <div class="status-title error">Payment Failed</div>
                <div class="status-message">
                    Unfortunately, your payment could not be processed. Please try again or contact support.
                </div>
                
                <div class="payment-details">
                    <div class="detail-row">
                        <span class="detail-label">Reference:</span>
                        <span class="detail-value">{{ $result->reference ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Provider:</span>
                        <span class="detail-value">{{ ucfirst($result->provider ?? 'N/A') }}</span>
                    </div>
                </div>
            @endif
        @else
            <div class="status-icon warning">⚠</div>
            <div class="status-title warning">Payment Status Unknown</div>
            <div class="status-message">
                We couldn't verify your payment status. Please contact support with your reference number.
            </div>
        @endif

        <div style="margin-top: 30px;">
            <a href="/" class="btn">Return Home</a>
            @if($result && $result->reference)
                <a href="/paygate/status/{{ $result->reference }}" class="btn btn-secondary" style="margin-left: 10px;">Check Status</a>
            @endif
        </div>
    </div>
</body>
</html>

