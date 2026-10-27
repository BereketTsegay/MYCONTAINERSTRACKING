<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Party extends Model
{
    //
    use LogsActivity;

    protected $guarded = [];

      public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'address', 'type'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
