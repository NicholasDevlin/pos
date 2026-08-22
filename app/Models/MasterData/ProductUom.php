<?php

namespace App\Models\MasterData;

use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductUom extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'units_of_measure_id',
        'sku',
        'conversion_factor',
        'cost_price',
        'base_price',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'units_of_measure_id');
    }

    public function tierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class);
    }
}
