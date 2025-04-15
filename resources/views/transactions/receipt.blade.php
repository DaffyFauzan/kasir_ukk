<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .header-section {
            margin-bottom: 20px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }
        .customer-info {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <h2>Transaction Receipt</h2>
        <p><strong>Transaction Date:</strong> {{ $transaction->sale_date }}</p>
        <p><strong>Transaction ID:</strong> #{{ $transaction->id }}</p>
    </div>

    <div class="customer-info">
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> {{ $transaction->customer->name ?? 'Non-Member' }}</p>
        <p><strong>Phone Number:</strong> {{ $transaction->customer->no_telp ?? 'N/A' }}</p>
        <p><strong>Member Status:</strong> {{ ucfirst($transaction->customer->status ?? 'Non-Member') }}</p>
        <p><strong>Joined Since:</strong> {{ $transaction->customer ? $transaction->customer->created_at->format('d M Y') : 'N/A' }}</p>
        <p><strong>Point Member:</strong> {{ $transaction->customer->poin ?? 0 }}</p>
        <p><strong>Staff:</strong> {{ $transaction->staff->name }}</p>
    </div>

    <h3>Products</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Points Earned</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->detailSales as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->amount }}</td>
                    <td>Rp {{ number_format($detail->product->price, 0, ',', '.') }}</td>
                    <td>{{ floor($detail->sub_total * 0.01) }}</td>
                    <td>Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total Price:</strong></td>
                <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
            </tr>
            @if($transaction->customer && $transaction->customer->status === 'old')
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Points Discount:</strong></td>
                    <td>-Rp {{ number_format($discount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Final Price:</strong></td>
                    <td>Rp {{ number_format($finalPrice ?? $transaction->total_price, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total Pay:</strong></td>
                <td>Rp {{ number_format($transaction->total_pay, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total Return:</strong></td>
                <td>Rp {{ number_format($transaction->total_return, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Points Earned:</strong></td>
                <td>{{ $transaction->poin }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 14px;">
        <p>Thank you for your purchase!</p>
    </div>
    <div style="margin-top: 20px; text-align: center; font-size: 12px;">
        <p>IndoApril</p>
    </div>
</body>
</html>
