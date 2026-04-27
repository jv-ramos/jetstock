<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

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

    public static function register(array $array): self
    {
        $product = new self();
        $product->fill($array);
        $product->save();

        return $product;
    }

    public static function change(int $id, array $array): self
    {
        $product = self::findOrFail($id);
        $product->fill($array);
        $product->save();

        return $product;
    }

    public static function findByName(string $name): ?self
    {
        if (empty($name)) {
            throw new \InvalidArgumentException(
                'Name must not be empty'
            );
        };

        $product = self::where('name', $name)->first();

        if (! $product) {
            throw new \RuntimeException(
                "Product with name $name not found"
            );
        } else {
            return $product;
        }
    }

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function ($product) {
            $product->validateUniqueName();
            $product->setAmountAttribute($product->amount);
        });
    }

    protected function validateUniqueName(): void
    {
        $query = self::where('name', $this->name);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        if ($query->exists()) {
            throw new \InvalidArgumentException(
                "Product with name $this->name already exists"
            );
        }
    }

    /**
     * ACCESSORS
     */
    public function getAmountAttribute($value): float
    {
        return $value / 100;
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function getAmountInCentsAttribute(): int
    {
        return (int) $this->attributes['amount'];
    }

    public function getQuantityAttribute($value): int
    {
        return (int) $value;
    }

    /**
     * MUTATORS
     *
     * @param mixed $value
     */
    public function setNameAttribute(string $value): void
    {
        if (strlen($value) < 3) {
            throw new \InvalidArgumentException(
                'Name must be at least 3 characters long.'
            );
        }

        if (strlen($value) > 50) {
            throw new \InvalidArgumentException(
                'Name must not exceed 50 characters'
            );
        }

        $this->attributes['name'] = $value;
    }

    public function setDescriptionAttribute(string $value): void
    {
        if (strlen($value) > 255) {
            throw new \InvalidArgumentException(
                'Description must not exceed 255 characters'
            );
        }

        $this->attributes['description'] = $value;
    }
    /**
     * @param mixed $value
     */
    public function setAmountAttribute($value): void
    {
        if (is_string($value)) {
            $value = (float) str_replace([',', ' ', 'R$'], '', $value);
        }

        if ($value <= 0) {
            throw new \InvalidArgumentException(
                'Amount must be greater than 0 (ZERO).'
            );
        }

        $this->attributes['amount'] = (int) round($value * 100);
    }
    /**
     * @param mixed $value
     */
    public function setQuantityAttribute($value): void
    {
        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if (! isset($value) || $value < 0) {
            throw new \InvalidArgumentException(
                'Quantity must be equals or greater than 0 (ZERO).'
            );
        }

        $this->attributes['quantity'] = $value;
    }

    /**
     * STOCK MANAGEMENT
     * @param mixed $value
     */
    // TODO: Create a stock history to log changes in stock
    public function stockIncrement($value = 1): void
    {
        if (is_string($value)) {
            $value = (int) str_replace([',', ' '], '', $value);
        }

        if ($value <= 0) {
            throw new \InvalidArgumentException(
                'Forbidden operation'
            );
        }

        $this->attributes['quantity'] += $value;
        $this->save();
    }
    /**
     * @param mixed $value
     */
    public function stockDecrement($value = 1): void
    {
        if (is_string($value)) {
            $value = (int) str_replace([',', ' '], '', $value);
        }

        if ($value <= 0) {
            throw new \InvalidArgumentException(
                'Forbidden operation'
            );
        }

        if (
            $this->attributes['quantity'] < 0
            || ($this->attributes['quantity'] - $value) < 0
        ) {
            throw new \RuntimeException(
                "Not enough {$this->attributes['name']} in stock"
            );
        }

        $this->attributes['quantity'] -= $value;
        $this->save();
    }
}
