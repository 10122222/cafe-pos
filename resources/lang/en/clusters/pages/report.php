<?php

return [
    'title' => 'Reports',

    'product' => [
        'title' => 'Product Reports',

        'table' => [
            'columns' => [
                'ordered' => 'Ordered',
            ],

            'summary' => [
                'least' => 'Least ordered product',
                'most' => 'Most ordered product',
            ],
        ],
    ],

    'sales' => [
        'title' => 'Sales Reports',

        'table' => [
            'columns' => [
                'ordered' => 'Ordered',
                'total_price' => 'Total',
            ],

            'summary' => [
                'min' => 'Min',
                'max' => 'Max',
                'sum' => 'Sum',
                'avg' => 'Avg',
            ],
        ],
    ],
];
