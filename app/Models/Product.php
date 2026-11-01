<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = ['category_id', 'name', 'sku', 'description', 'hs_code', 'has_variations'];

    public function pkCategory(): BelongsTo { return $this->belongsTo(Category::class, 'pk_category_id'); }
    public function hbillCategory(): BelongsTo { return $this->belongsTo(Category::class, 'hbill_category_id'); }
    public function edpassCategory(): BelongsTo { return $this->belongsTo(Category::class, 'edpass_category_id'); }
    
    public function variations(): HasMany { return $this->hasMany(ProductVariation::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'sku', 'hs_code'])->logOnlyDirty()->dontLogEmptyChanges();
    }
}
