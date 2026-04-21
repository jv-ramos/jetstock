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

    /*
    * ACCESSORS
    */
    public function getAmountAttribute($value)
    {
        return $value / 100;
    }

    public function getFormattedAmountAttribute()
    {
        return 'R$ '.number_format($this->amount, 2, ',', '.');
    }

    public function getAmountInCentsAttribute()
    {
        return (int) $this->attributes['amount'];
    }

    public function getQuantityAttribute($value)
    {
        return (int) $value;
    }

    /*
    * MUTATORS
    */
    public function setAmountAttribute($value)
    {
        if (is_string($value)) {
            $value = (float) str_replace([',', ' ', 'R$'], '', $value);
        }

        if ($value < 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0 (ZERO).');
        }

        $this->attributes['amount'] = (int) round($value * 100);
    }

    public function setQuantityAttribute($value)
    {
        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if ($value < 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0 (ZERO).');
        }

        $this->attributes['quantity'] = $value;
    }

    public function stockIncrement($value = 1)
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

        if ($this->attributes['quantity'] < 0 || ($this->attributes['quantity'] - $value) < 0)
        {
            return "Not enough {$this->name} in stock";
        }

        $this->attributes['quantity'] -= $value;
    }
}
