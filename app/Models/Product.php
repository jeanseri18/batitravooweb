<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public const UNIT_PIECE = 'piece';

    public const UNIT_LINEAR_METER = 'linear_meter';

    public const UNIT_SQUARE_METER = 'square_meter';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'image_path',
        'price_amount',
        'stock_units',
        'unit_of_measure',
        'views_count',
        'status',
        'admin_notes',
    ];

    /** @return array<string, string> */
    public static function unitOptions(): array
    {
        return [
            self::UNIT_PIECE => 'À l\'unité',
            self::UNIT_LINEAR_METER => 'Mètre linéaire',
            self::UNIT_SQUARE_METER => 'Mètre carré',
        ];
    }

    public function unitLabel(): string
    {
        $key = $this->unit_of_measure ?: self::UNIT_PIECE;

        return self::unitOptions()[$key] ?? self::unitOptions()[self::UNIT_PIECE];
    }

    public function stockShortLabel(): string
    {
        $qty = (int) $this->stock_units;
        $unit = $this->unit_of_measure ?: self::UNIT_PIECE;

        return match ($unit) {
            self::UNIT_LINEAR_METER => "Stock : $qty ml",
            self::UNIT_SQUARE_METER => "Stock : $qty m²",
            default => "Stock : $qty",
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
