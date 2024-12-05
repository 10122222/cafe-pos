<?php

return [

    'single' => 'Order',
    'plural' => 'Orders',
    'nav.group' => 'Transactions',

    // Form labels
    'number' => 'Number',
    'customer' => 'Customer',
    'customer.name' => 'Name',
    'status' => 'Status',
    'total' => 'Total',
    'created_at' => 'Created At',
    'updated_at' => 'Last Modified At',
    'details' => 'Order Details',
    'items' => 'Order Items',
    'date' => 'Order Date',
    'notes' => 'Notes',
    'cash' => 'Cash',

    // Actions
    'actions' => [
        'reset' => 'Reset',
        'pdf' => 'PDF',
        'create_customer' => 'Create customer',
        'add_payment' => 'Add payment',
    ],

    // Messages
    'messages' => [
        'reset_confirmation' => 'Are you sure?',
        'reset_description' => 'All existing items will be removed from the order.',
    ],

    // Item related
    'item' => [
        'product' => 'Product',
        'quantity' => 'Quantity',
        'unit_price' => 'Price',
        'increase' => 'Increase',
        'decrease' => 'Decrease',
        'reset_qty' => 'Reset quantity',
        'open_product' => 'Open product',
    ],

    // Filters
    'filters' => [
        'created_from' => 'Order from',
        'created_until' => 'Order until',
    ],

    'notifications' => [
        'new' => [
            'title' => 'New order',
            'body' => ':customer ordered :count products.',
        ],
    ],

    'stat' => [
        'orders' => 'Orders',
        'open_orders' => 'Open orders',
        'avg_price' => 'Average price',
    ],

    'tabs' => [
        'all' => 'All',
        'new' => 'New',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

];
