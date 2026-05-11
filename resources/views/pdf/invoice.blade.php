<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    .header { background: #1a5276; color: #fff; padding: 12px 20px; }
    .header h1 { font-size: 20px; }
    .header .sub { font-size: 10px; opacity: 0.85; margin-top: 3px; }
    .invoice-meta { padding: 10px 20px; display: flex; justify-content: space-between; }
    .invoice-meta .box { border: 1px solid #ccc; padding: 8px 14px; border-radius: 4px; }
    .invoice-meta .box p { margin: 2px 0; font-size: 10.5px; }
    table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
    table.items th { background: #2980b9; color: #fff; padding: 6px 10px; text-align: left; font-size: 10px; }
    table.items td { padding: 5px 10px; border-bottom: 1px solid #eee; }
    table.items tr:nth-child(even) td { background: #f8f9fa; }
    .totals { float: right; width: 260px; margin: 0 20px; }
    .totals table { width: 100%; border-collapse: collapse; }
    .totals td { padding: 4px 8px; font-size: 11px; }
    .totals .grand { font-weight: bold; font-size: 13px; background: #d6eaf8; }
    .status-paid { color: green; font-weight: bold; font-size: 22px; border: 3px solid green; padding: 2px 12px; display: inline-block; transform: rotate(-15deg); margin: 10px 20px; }
    .footer { border-top: 1px solid #ccc; margin-top: 40px; padding: 10px 20px; font-size: 9px; color: #555; text-align: center; }
</style>
</head>
<body>

<div class="header">
    <h1>{{ strtoupper($lab->name) }} — INVOICE</h1>
    @if ($lab->address)
    <div class="sub">{{ $lab->address }}{{ $lab->phone ? ' | Phone: ' . $lab->phone : '' }}</div>
    @endif
</div>

<div class="invoice-meta">
    <div class="box">
        <p><strong>Invoice No:</strong> {{ $bill->bill_uid }}</p>
        <p><strong>Order No:</strong> {{ $order->order_uid }}</p>
        <p><strong>Date:</strong> {{ $bill->created_at->format('d M Y') }}</p>
    </div>
    <div class="box">
        <p><strong>Patient:</strong> {{ $patient->name }}</p>
        <p><strong>ID:</strong> {{ $patient->patient_uid }}</p>
        <p><strong>Phone:</strong> {{ $patient->phone }}</p>
        <p><strong>Referred By:</strong> {{ $patient->referred_by ?? 'Self' }}</p>
    </div>
</div>

<table class="items">
    <thead>
        <tr>
            <th>#</th>
            <th>Test Name</th>
            <th>Code</th>
            <th style="text-align:right">Price (₹)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->test->test_name }}</td>
            <td>{{ $item->test->test_code }}</td>
            <td style="text-align:right">{{ number_format($item->price_at_order, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <table>
        <tr><td>Subtotal</td><td style="text-align:right">₹ {{ number_format($bill->subtotal, 2) }}</td></tr>
        @if ($bill->discount_value > 0)
        <tr><td>Discount ({{ $bill->discount_type === 'percent' ? $bill->discount_value . '%' : 'Flat' }})</td>
            <td style="text-align:right">- ₹ {{ number_format($bill->subtotal - $bill->total_amount, 2) }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td style="text-align:right">₹ {{ number_format($bill->total_amount, 2) }}</td></tr>
        <tr><td>Amount Paid</td><td style="text-align:right">₹ {{ number_format($bill->amount_paid, 2) }}</td></tr>
        <tr><td>Balance Due</td><td style="text-align:right">₹ {{ number_format($bill->total_amount - $bill->amount_paid, 2) }}</td></tr>
    </table>
</div>

<div style="clear:both; padding: 10px 20px;">
    <strong>Payment Method:</strong> {{ $bill->payment_method ? ucfirst($bill->payment_method) : 'N/A' }}
    &nbsp;&nbsp;&nbsp;
    <strong>Status:</strong>
    @if ($bill->payment_status === 'paid')
        <span style="color:green; font-weight:bold">PAID</span>
    @elseif ($bill->payment_status === 'partial')
        <span style="color:orange; font-weight:bold">PARTIALLY PAID</span>
    @else
        <span style="color:red; font-weight:bold">UNPAID</span>
    @endif
</div>

<div class="footer">
    Thank you for choosing our lab services. This is a computer-generated invoice.
</div>
</body>
</html>
