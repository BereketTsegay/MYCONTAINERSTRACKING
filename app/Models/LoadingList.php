<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class LoadingList extends Model
{
    use LogsActivity;

    protected $fillable = ['container_id', 'voyage_id', 'loading_date', 'loader_name', 'document_status'];

    // Direct Inverse Relationship Hooks
    public function container(): BelongsTo { return $this->belongsTo(Container::class); }
    public function voyage(): BelongsTo { return $this->belongsTo(Voyage::class); }
    public function items(): HasMany { return $this->hasMany(LoadingListItem::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['container_id', 'document_status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
