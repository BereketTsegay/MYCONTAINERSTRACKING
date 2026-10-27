<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Voyage extends Model
{
    use LogsActivity;

    protected $fillable = [
        'vessel_id', 'voyage_number', 'party_id', 'batch_id',
        'departure_date', 'arrival_date', 'origin_port', 'destination_port'
    ];

    public function vessel(): BelongsTo { return $this->belongsTo(Vessel::class); }
    public function party(): BelongsTo { return $this->belongsTo(Party::class); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['vessel_id', 'voyage_number', 'party_id', 'batch_id', 'origin_port', 'destination_port'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
