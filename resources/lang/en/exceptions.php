<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exception language lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for various custom exception handling
    | messages that we need to display to the user.
    |
    |
    */

    'route_not_found' => 'Route not found.',
    'access_denied'     => 'Access denied',
    'invalid_request' => 'Invalid request.',
    'internal_custom' => 'Internal server error #:code.',
    'authentication' => 'You are not authorized.',

    /*
     * PURCHASES
     */
    'purchases' => [
        'card_declined' => 'The card was declined.',
        'rate_limit' => 'Too many requests.',
        'same_seller' => 'You must purchase products from the same seller.',
        'buy_products' => 'You cannot buy some products.',
        'buy_own_products' => 'You cannot buy your own products.',
        'already_bought_product' => 'You have already bought this product.',
        'seller_not_confirmed' => 'The seller account is not confirmed.',
        'different_currency' => 'The currency must be the same for all the products.'
    ],
    'stores' => [
        'cannot_create_more_stores' => 'You cannot create more than one store.'
    ]
];
