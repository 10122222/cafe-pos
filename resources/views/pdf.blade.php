<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
    </style>
</head>

<body>
<div class="invoice-box">
    <table>
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <h1>Invoice</h1>
                        </td>
                        <td>
                            Invoice #: {{ $record->number }}<br>
                            Created: {{ $record->created_at->format('M d, Y') }}<br>
                            Due: {{ $record->created_at->addDays(30)->format('M d, Y') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="information">
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            {{ $record->customer->name }}<br>
                            {{ $record->customer->address }}<br>
                            {{ $record->customer->email }}
                        </td>
                        <td>
                            Your Company Name<br>
                            yourcompany@example.com
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="heading">
            <td>Payment Method</td>
            <td>Check #</td>
        </tr>
        <tr class="details">
            <td>Cash</td>
            <td>{{ $record->payment->first()->amount ?? 'N/A' }}</td>
        </tr>
        <tr class="heading">
            <td>Item</td>
            <td>Price</td>
        </tr>
        @foreach ($record->items as $item)
            <tr class="item">
                <td>{{ $item->product->name }}</td>
                <td>Rp {{ number_format($item->unit_price * $item->qty, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td></td>
            <td>Total: Rp {{ number_format($record->total_price, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>
</body>

</html>
