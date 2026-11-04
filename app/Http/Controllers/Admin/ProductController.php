<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\UnitOfMeasurement;
use App\Http\Requests\Admin\StoreOrUpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['pkCategory', 'hbillCategory', 'edpassCategory', 'variations.uom']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('hs_code', 'LIKE', "%{$search}%");
            });
        }

        // Optional filtering optimization adjustment
        if ($request->filled('category_id')) {
            $catId = $request->category_id;
            $query->where(function($q) use ($catId) {
                $q->where('pk_category_id', $catId)
                  ->orWhere('hbill_category_id', $catId)
                  ->orWhere('edpass_category_id', $catId);
            });
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function lookups()
    {
        return response()->json([
            'categories' => Category::all(),
            'uoms' => UnitOfMeasurement::all()
        ]);
    }

    public function store(StoreOrUpdateProductRequest $request)
    {
        return DB::transaction(function () use ($request) {
            
           
            $product = $this->save($request);

            if ($request->has_variations && $request->has('variations')) {
                $product->variations()->createMany($request->input('variations'));
            }

            // CORRECTED: Load the matching split relations before returning JSON payload
            return response()->json([
                'status' => 'success', 
                'data' => $product->load(['pkCategory', 'hbillCategory', 'edpassCategory', 'variations.uom'])
            ], 201);
        });
    }

    public function save(StoreOrUpdateProductRequest $request)
    {
        $product = !!$request->id ? Product::find($request->id) : new Product();

        $product->fill($request->except('variations'));

        $product->pk_category_id = $request->pk_category_id;
        $product->hbill_category_id = $request->hbill_category_id;
        $product->edpass_category_id = $request->edpass_category_id;
        
        $product->save();

        return $product;
    }

    public function update(StoreOrUpdateProductRequest $request, Product $product)
    {
         return DB::transaction(function () use ($request, $product) {
            // Update the master product attributes safely
            $product = $this->save($request);

            if ($request->has_variations && $request->has('variations')) {
                $incomingIds = collect($request->input('variations'))->pluck('id')->filter()->toArray();
                
                // Remove variants deleted from the matrix frontend drawer panel
                $product->variations()->whereNotIn('id', $incomingIds)->delete();

                foreach ($request->input('variations') as $vData) {
                    
                    // CLEANUP PATTERN FOR POSTGRESQL/ELOQUENT:
                    // Explicitly strip away loaded relationship array nodes so they don't corrupt the SQL string
                    unset($vData['uom']); 
                    unset($vData['created_at']);
                    unset($vData['updated_at']);

                    if (isset($vData['id']) && !empty($vData['id'])) {
                        // Safely updates matching database row columns only
                        $product->variations()->where('id', $vData['id'])->update($vData);
                    } else {
                        // Creates clean records for newly appended row layers
                        $product->variations()->create($vData);
                    }
                }
            } else {
                // If checkbox is toggled off, drop associated parameters completely
                $product->variations()->delete();
            }

            // Hydrate and return clean updated data packages back to Vue
            return response()->json([
                'status' => 'success', 
                'data' => $product->load(['pkCategory', 'hbillCategory', 'edpassCategory', 'variations.uom'])
            ]);
        });
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['status' => 'success']);
    }
}
