<?php

namespace App\Models\Transaction;

use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SaleItem extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    protected $fillable = [
        'sale_id',
        'product_uom_id',
        'product_name',
        'uom',
        'quantity',
        'price',
        'discount_percentage',
        'discount_nominal',
    ];
}
