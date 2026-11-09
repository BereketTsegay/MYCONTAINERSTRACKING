<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoadingListItem extends Model
{
    protected $table = 'loading_list_items';

    protected $fillable = [
        'loading_list_id', 'product_id', 'declared_item_name', 
        'uom_code', 'total_qty', 'sector_allocations'
    ];

    /**
     * The attributes that should be cast.
     * Automatically parses JSON sector logs (A1-A12, H1-H12) into manageable PHP arrays.
     */
    protected $casts = [
        'sector_allocations' => 'array',
    ];

    public function loadingList(): BelongsTo { return $this->belongsTo(LoadingList::class, 'loading_list_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
