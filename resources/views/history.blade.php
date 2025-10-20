<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Paygate</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .filters {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        .form-control {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-successful {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .pagination {
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .pagination a:hover {
            background: #0056b3;
        }
        .pagination .current {
            background: #6c757d;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment History</h1>
        </div>

        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="successful" {{ request('status') == 'successful' ? 'selected' : '' }}>Successful</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="provider">Provider</label>
                    <select name="provider" id="provider" class="form-control">
                        <option value="">All Providers</option>
                        <option value="paystack" {{ request('provider') == 'paystack' ? 'selected' : '' }}>Paystack</option>
                        <option value="gtpay" {{ request('provider') == 'gtpay' ? 'selected' : '' }}>GTPay</option>
                        <option value="flutterwave" {{ request('provider') == 'flutterwave' ? 'selected' : '' }}>Flutterwave</option>
                        <option value="monnify" {{ request('provider') == 'monnify' ? 'selected' : '' }}>Monnify</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="customer_email">Email</label>
                    <input type="email" name="customer_email" id="customer_email" class="form-control" 
                           value="{{ request('customer_email') }}" placeholder="Customer email">
                </div>

                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>

                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>

                <button type="submit" class="btn">Filter</button>
                <a href="/paygate/history" class="btn" style="background: #6c757d;">Clear</a>
            </form>
        </div>

        <div class="table-container">
            @if($result && count($result->data) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Provider</th>
                            <th>Amount</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result->data as $payment)
                            <tr>
                                <td>{{ $payment->reference }}</td>
                                <td>{{ ucfirst($payment->provider) }}</td>
                                <td>₦{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->customer_email }}</td>
                                <td>
                                    <span class="status status-{{ $payment->status }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y H:i') }}</td>
                                <td>
                                    <a href="/paygate/status/{{ $payment->reference }}" class="btn" style="padding: 4px 8px; font-size: 12px;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">
                    <h3>No payments found</h3>
                    <p>No payment records match your current filters.</p>
                </div>
            @endif
        </div>

        @if($result && $result->last_page > 1)
            <div class="pagination">
                @if($result->current_page > 1)
                    <a href="?page={{ $result->current_page - 1 }}&{{ http_build_query(request()->except('page')) }}">Previous</a>
                @endif

                @for($i = 1; $i <= $result->last_page; $i++)
                    @if($i == $result->current_page)
                        <span class="current">{{ $i }}</span>
                    @else
                        <a href="?page={{ $i }}&{{ http_build_query(request()->except('page')) }}">{{ $i }}</a>
                    @endif
                @endfor

                @if($result->current_page < $result->last_page)
                    <a href="?page={{ $result->current_page + 1 }}&{{ http_build_query(request()->except('page')) }}">Next</a>
                @endif
            </div>
        @endif
    </div>
</body>
</html>