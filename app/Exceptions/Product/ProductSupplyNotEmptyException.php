<?php

namespace App\Exceptions\Product;

class ProductSupplyNotEmptyException extends ProductException
{
    protected string $customMessage;

    public function __construct(string $customMessage)
    {
        parent::__construct($customMessage);
    }
}
