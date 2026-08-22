<?php

namespace App\Models\MasterData;

use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductTierPrice extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    protected $table = 'product_tier_prices';

    protected $fillable = [
        'product_uom_id',
        'tier',
        'price',
    ];
}
