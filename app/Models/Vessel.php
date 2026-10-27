<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Vessel extends Model
{
    //
    use LogsActivity;

    protected $guarded = [];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'imo_number', 'party_id', 'mmsi_number',
                'call_sign', 'flag', 'vessel_type', 'gross_tonnage', 'net_tonnage'
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
