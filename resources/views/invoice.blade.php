<!DOCTYPE html>
<html lang="en">
    <head>
        <meta
            http-equiv="Content-Type"
            content="text/html; charset=UTF-8"
        />
        <meta
            name="viewport"
            content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
        />
        <meta
            http-equiv="X-UA-Compatible"
            content="ie=edge"
        />
        <title>
            INVOICE-{{ $order->number }}-{{ $order->created_at->format('d-m-Y') }}
        </title>
        <style>
            table {
                width: 100%;
            }

            th,
            td {
                padding: 0.125rem;
            }

            .font-sm {
                font-size: 0.875rem; /* 14px */
                line-height: 1.25rem; /* 20px */
            }

            .font-base {
                font-size: 1rem; /* 16px */
                line-height: 1.5rem; /* 24px */
            }

            .font-lg {
                font-size: 1.125rem; /* 18px */
                line-height: 1.75rem; /* 28px */
            }

            .font-xl {
                font-size: 1.25rem; /* 20px */
                line-height: 1.75rem; /* 28px */
            }

            .font-2xl {
                font-size: 1.5rem; /* 24px */
                line-height: 2rem; /* 32px */
            }

            .left {
                text-align: left;
            }

            .right {
                text-align: right;
            }

            .center {
                text-align: center;
            }

            .upper {
                text-transform: uppercase;
            }

            hr {
                border: none;
                border-top: 1px dashed #000;
            }
        </style>
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th
                        colspan="2"
                        class="font-2xl upper"
                    >
                        {{ config('app.name') }}
                    </th>
                </tr>
                <tr>
                    <th colspan="2">
                        <hr />
                    </th>
                </tr>

                <tr>
                    <th class="font-base left">NUMBER</th>
                    <td class="font-sm right">
                        {{ $order->number }}
                    </td>
                </tr>
                <tr>
                    <th class="font-base left">CUSTOMER</th>
                    <td class="font-sm right upper">
                        {{ $order->customer->name }}
                    </td>
                </tr>
                <tr>
                    <th class="font-base left">DATE</th>
                    <td class="font-sm right">
                        {{ $order->created_at->format('d-m-Y H:i:s') }}
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <hr />
                    </th>
                </tr>

                <tr></tr>
                <tr>
                    <th
                        colspan="2"
                        class="font-xl"
                    >
                        ITEMS
                    </th>
                </tr>
                <tr>
                    <th colspan="2">
                        <hr />
                    </th>
                </tr>

                <tr></tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td class="font-sm">
                            <strong class="upper">
                                {{ $item->product->name }}
                            </strong>
                            <br />
                            {{ number_format($item->unit_price) }} x
                            {{ $item->qty }}
                        </td>
                        <td class="font-sm right">
                            {{ number_format($item->qty * $item->unit_price) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">
                        <hr />
                    </th>
                </tr>

                <tr></tr>
                <tr>
                    <th class="font-base left">TOTAL PRICE</th>
                    <td class="font-sm right">
                        {{ number_format($order->total_price) }}
                    </td>
                </tr>
                <tr>
                    <th class="font-base left">CASH</th>
                    <td class="font-sm right">
                        {{ number_format($order->payment->amount) }}
                    </td>
                </tr>
                <tr>
                    <th class="font-base left">CHANGE</th>
                    <td class="font-sm right">
                        {{
                            number_format(
                                $order->payment->amount -
                                    $order->total_price,
                            )
                        }}
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <hr />
                    </th>
                </tr>
                <tr>
                    <th
                        colspan="2"
                        class="font-base"
                    >
                        THANK YOU
                    </th>
                </tr>
                <tr>
                    <th
                        colspan="2"
                        class="font-sm"
                    >
                        HAVE A NICE DAY
                    </th>
                </tr>
            </tfoot>
        </table>
    </body>
</html>
