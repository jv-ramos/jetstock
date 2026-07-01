<?php

namespace Database\Seeders\services;

use Illuminate\Support\Facades\Http;

class FetchApi
{
    public static function getProducts()
    {
        $response = Http::get('https://fakestoreapi.com/products', '?page=1');

        return $response->json();
    }
}
