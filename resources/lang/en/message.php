<?php

return [
    'products' => [
        'name_unchangeable' => 'Product name cannot be changed',
        'name_required' => 'Name must not be empty.',
        'name_not_found' => 'Product with name :name not found.',
        'name_unique' => 'Product with name :name already exists',
        'min_name_length' => 'Name must be at least :min characters long.',
        'max_name_length' => 'Name must not exceed :max characters',
        'max_description_length' => 'Description must not exceed :max characters',
        'amount_required' => 'Amount must be greater than 0 (ZERO).',
        'quantity_required' => 'Quantity must be equals or greater than 0 (ZERO).',
        'quantity_exceed' => 'Not enough :name in stock. Only :quantity available',
        'cannot_delete_qty' => 'Cannot delete product with quantity greater than 0',
        'not_found' => 'Product not found.',
    ],
    'general' => [
        'fbd_op' => 'Forbidden operation',
    ],
];
