<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Container extends Model
{
    use LogsActivity;

    protected $fillable = [
        'container_number', 'container_name', 'container_size', 'container_type',
        'booking_number', 'seal_number', 'voyage_id', 'transitor_id', 'shipper_id',
        'batch_id', 'vgm', 'tare_weight', 'max_gross_weight', 'total_pkg', 'status'
    ];

    // Boot hook to automatically assign immutable security UUIDs upon initialization
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    // Explicit Relational Definitions
    public function voyage(): BelongsTo { return $this->belongsTo(Voyage::class); }
    public function transitor(): BelongsTo { return $this->belongsTo(Party::class, 'transitor_id'); }
    public function shipper(): BelongsTo { return $this->belongsTo(Party::class, 'shipper_id'); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['container_number', 'booking_number', 'seal_number', 'status', 'voyage_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
