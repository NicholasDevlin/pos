<?php

namespace App\Models\Transaction;

use App\Traits\HasActivityLogOptions;
use App\Traits\HasDateSerialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Receipt extends Model
{
    use HasActivityLogOptions, HasDateSerialization, HasFactory, LogsActivity;

    protected $fillable = [
        'sale_id',
        'paid_amount',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
