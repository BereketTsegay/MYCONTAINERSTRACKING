<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ProductVariation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'product_id', 'unit_of_measurement_id', 'qty_per_unit', 'variation_sku',
        'price', 'weight', 'length', 'width', 'height', 'variation_hs_code'
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasurement::class, 'unit_of_measurement_id'); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['variation_sku', 'price', 'variation_hs_code'])->logOnlyDirty()->dontLogEmptyChanges();
    }
}
