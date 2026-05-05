<?php

namespace App\Models;

use App\Constants;
use App\Exceptions\Product\InvalidProductAttributeException;
use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Product\ProductSupplyNotEmptyException;
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

    protected $attributes = ['amount' => 0, 'quantity' => 0];

    protected $casts = ['amount' => 'integer', 'quantity' => 'integer'];

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function ($product) {
            $product->validateUniqueName();
            $product->setAmountAttribute($product->amount);
            $product->setQuantityAttribute($product->quantity);
        });
    }

    protected function validateUniqueName(): void
    {
        $query = self::where('name', $this->name);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        if ($query->exists()) {
            throw new InvalidProductAttributeException(
                __('message.products.name_unique', ['name' => $this->name])
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

    /**
     * MUTATORS
     */
    public function setNameAttribute(string $value): void
    {
        if (strlen($value) < Constants::NAME_MIN_CHAR) {
            throw new InvalidProductAttributeException(
                __('message.products.min_name_length', ['min' => Constants::NAME_MIN_CHAR])
            );
        }

        if (strlen($value) > Constants::NAME_MAX_CHAR) {
            throw new InvalidProductAttributeException(
                __('message.products.max_name_length', ['max' => Constants::NAME_MAX_CHAR])
            );
        }

        $this->attributes['name'] = $value;
    }

    public function setDescriptionAttribute(string $value): void
    {
        if (strlen($value) > Constants::DESC_MAX_CHAR) {
            throw new InvalidProductAttributeException(
                __('message.products.max_description_length', ['max' => Constants::DESC_MAX_CHAR])
            );
        }

        $this->attributes['description'] = $value;
    }

    public function setAmountAttribute($value): void
    {
        if (is_string($value)) {
            $value = (float) str_replace([',', ' ', 'R$'], '', $value);
        }

        $this->isLessThanOrEqualsZero($value);

        $this->attributes['amount'] = (int) round($value * 100);
    }

    public function setQuantityAttribute($value): void
    {
        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if (! isset($value) || $value < 0) {
            throw new InvalidProductAttributeException(
                __('message.products.quantity_required')
            );
        }

        $this->attributes['quantity'] = $value;
    }

    /**
     * CRUD METHODS
     */
    public static function register(array $array): self
    {
        $product = new self;
        $product->fill($array);
        $product->save();

        return $product;
    }

    public static function change(int $id, array $array): self
    {
        $product = self::findOrFail($id);

        if (isset($array['name']) && $product->name !== $array['name']) {
            throw new InvalidProductAttributeException(
                __('message.products.name_unchangeable')
            );
        }

        $product->fill($array);
        $product->save();

        return $product;
    }

    public static function remove(string $name): void
    {
        $query = self::findByName($name);

        if (! $query) {
            throw new ProductNotFoundException(
                __('message.products.name_not_found', ['name' => $name])
            );
        }

        if ($query->quantity > 0) {
            throw new ProductSupplyNotEmptyException(
                __('message.products.cannot_delete_qty')
            );
        }

        $query->delete();
    }

    public static function findByName(string $name): ?self
    {
        if (empty($name)) {
            throw new InvalidProductAttributeException(
                __('message.products.name_required')
            );
        }

        $product = self::where('name', $name)->first();

        if (! $product) {
            throw new ProductNotFoundException(
                __('message.products.name_not_found', ['name' => $name])
            );
        } else {
            return $product;
        }
    }

    /**
     * STOCK METHODS
     */
    public function stockUpdate(array $order): void
    {
        $this->isLessThanOrEqualsZero($order['quantity']);
        $this->validateStock($this, $order['quantity']);

        switch ($order['operation']) {
            case true:
                $this->attributes['quantity'] += $order['quantity'];
                break;
            case false:
                $this->attributes['quantity'] -= $order['quantity'];
                break;
            default:
                break;
        }

        $this->save();
    }

    public function calculateTotalInventoryValue(): float
    {
        return (self::query()
            ->selectRaw('SUM(amount * quantity) as total')
            ->value('total') / 100) ?? 0;
    }

    private function validadeOrderItem(array $item): void
    {
        if (empty($item['name'])) {
            throw new InvalidProductAttributeException(
                __('message.products.name_required')
            );
        }

        if (! is_int($item['quantity'] ?? null)) {
            throw new InvalidProductAttributeException(
                __('message.general.fbd_op')
            );
        }
    }

    private function validateStock(Product $product, int $requestedQuantity): void
    {
        if (
            $this->isLessThanOrEqualsZero($product->quantity)
            || $product->quantity < $requestedQuantity
        ) {
            throw new InvalidProductAttributeException(
                __(
                    'message.products.quantity_exceed',
                    [
                        'name' => $product->name,
                        'quantity' => $product->quantity,
                    ]
                )
            );
        }
    }

    private function isLessThanOrEqualsZero($value): void
    {
        if ($value <= 0) {
            throw new InvalidProductAttributeException(
                __('message.general.fbd_op')
            );
        }
    }
}
