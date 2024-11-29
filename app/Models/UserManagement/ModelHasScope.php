<?php

namespace App\Models\UserManagement;

use App\Traits\HasActivityLogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Traits\LogsActivity;

class ModelHasScope extends Model
{
    use HasActivityLogOptions, HasFactory, LogsActivity;

    protected $fillable = [
        'location_id',
        'business_unit_id',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}
