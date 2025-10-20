<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Paygate</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-title {
            font-size: 28px;
            font-weight: 600;
            color: #28a745;
            margin-bottom: 10px;
        }

        .success-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
            font-size: 16px;
        }

        .payment-details {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .detail-value {
            color: #666;
            font-size: 14px;
        }

        .amount {
            font-size: 24px;
            font-weight: 700;
            color: #28a745;
        }

        .reference {
            font-family: monospace;
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
            color: white;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #1e7e34;
        }

        .download-section {
            margin-top: 30px;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }

        .download-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .download-links {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .download-btn {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .download-btn:hover {
            background: #0056b3;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <div class="success-title">Payment Successful!</div>
        <div class="success-message">
            Your payment has been processed successfully. You will receive a confirmation email shortly.
        </div>

        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Reference Number:</span>
                <span class="detail-value reference">{{ $payment->reference }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value amount">₦{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">{{ ucfirst($payment->payment_method ?? 'N/A') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Provider:</span>
                <span class="detail-value">{{ ucfirst($payment->provider) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer Email:</span>
                <span class="detail-value">{{ $payment->customer_email }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Transaction Date:</span>
                <span
                    class="detail-value">{{ \Carbon\Carbon::parse($payment->completed_at)->format('M d, Y H:i:s') }}</span>
            </div>
        </div>

        <div class="actions">
            <a href="/" class="btn btn-success">Continue Shopping</a>
            <a href="/paygate/history" class="btn btn-secondary">View Payment History</a>
            <a href="/paygate/status/{{ $payment->reference }}" class="btn">Check Status</a>
        </div>

        <div class="download-section">
            <div class="download-title">Download Receipt</div>
            <div class="download-links">
                <a href="/paygate/receipt/{{ $payment->reference }}?format=pdf" class="download-btn">PDF Receipt</a>
                <a href="/paygate/receipt/{{ $payment->reference }}?format=email" class="download-btn">Email Receipt</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect after 30 seconds (optional)
        // setTimeout(() => {
        //     window.location.href = '/';
        // }, 30000);
    </script>
</body>

</html>
