<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'quantity',
    ];

    protected $attributes = [
        'amount' => 0,
        'quantity' => 0,
    ];

    protected $casts = [
        'amount' => 'integer',
        'quantity' => 'integer',
    ];

    public static function register(array $array): void
    {
        if (strlen($array['name']) < 3) {
            throw new \InvalidArgumentException(
                'Name must be at least 3 characters long.'
            );
        }

        if (strlen($array['name']) > 50) {
            throw new \InvalidArgumentException(
                'Name must not exceed 50 characters'
            );
        }

        if (isset($array['amount']) && $array['amount'] < 0) {
            throw new \InvalidArgumentException(
                'Amount must be greater than 0 (ZERO).'
            );
        }

        if (isset($array['quantity']) && $array['quantity'] < 0) {
            throw new \InvalidArgumentException(
                'Quantity must be greater than 0 (ZERO).'
            );
        }

        Product::create([
            'name' => $array['name'],
            'description' => $array['description'],
            'amount' => $array['amount'],
            'quantity' => $array['quantity'],
        ]);
    }

    /*
    * ACCESSORS
    */
    public function getAmountAttribute($value): float
    {
        return $value / 100;
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'R$ '.number_format($this->amount, 2, ',', '.');
    }

    public function getAmountInCentsAttribute(): int
    {
        return (int) $this->attributes['amount'];
    }

    public function getQuantityAttribute($value): int
    {
        return (int) $value;
    }

    /*
    * MUTATORS
    */
    public function setAmountAttribute($value): void
    {
        if (is_string($value)) {
            $value = (float) str_replace([',', ' ', 'R$'], '', $value);
        }

        if ($value < 0) {
            throw new \InvalidArgumentException(
                'Amount must be greater than 0 (ZERO).'
            );
        }

        $this->attributes['amount'] = (int) round($value * 100);
    }

    public function setQuantityAttribute($value): void
    {
        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if ($value < 0) {
            throw new \InvalidArgumentException(
                'Quantity must be greater than 0 (ZERO).'
            );
        }

        $this->attributes['quantity'] = $value;
    }

    /*
    * STOCK MANAGEMENT
    */
    // TODO: Create a stock history to log changes in stock
    public function stockIncrement($value = 1): void
    {
        if (is_string($value)) {
            $value = (int) str_replace([',', ' '], '', $value);
        }

        if ($value <= 0) {
            throw new \InvalidArgumentException('Forbidden operation');
        }

        $this->attributes['quantity'] += $value;
    }

    public function stockDecrement($value = 1)
    {
        if (is_string($value)) {
            $value = (int) str_replace([',', ' '], '', $value);
        }

        if ($value <= 0) {
            throw new \InvalidArgumentException('Forbidden operation');
        }

        if ($this->attributes['quantity'] < 0 ||
            ($this->attributes['quantity'] - $value) < 0
        ) {
            throw new \RuntimeException("Not enough {$this->name} in stock");
        }

        $this->attributes['quantity'] -= $value;
    }
}
