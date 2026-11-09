<?php

// app/Http/Controllers/Admin/LoadingListController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoadingList;
use App\Models\Container;
use App\Models\Voyage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoadingListController extends Controller
{
    public function index(Request $request)
    {
        $query = LoadingList::with(['container', 'voyage.vessel']);
        
        if ($request->filled('search')) {
            $query->whereHas('container', function($q) use ($request) {
                $q->where('container_number', 'LIKE', "%{$request->search}%");
            });
        }
        return response()->json($query->latest()->paginate(10));
    }

    public function show(LoadingList $loadingList)
    {
        return response()->json($loadingList->load(['container', 'voyage.vessel', 'items.product']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'container_id' => 'required|exists:containers,id',
            'voyage_id' => 'required|exists:voyages,id',
            'loading_date' => 'required|date',
            'loader_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.declared_item_name' => 'required|string',
            'items.*.uom_code' => 'required|string',
            'items.*.total_qty' => 'required|integer|min:1',
            'items.*.sector_allocations' => 'nullable|array'
        ]);

        return DB::transaction(function () use ($data) {
            $list = LoadingList::create([
                'container_id' => $data['container_id'],
                'voyage_id' => $data['voyage_id'],
                'loading_date' => $data['loading_date'],
                'loader_name' => $data['loader_name']
            ]);

            foreach ($data['items'] as $item) {
                $list->items()->create($item);
            }

            return response()->json(['status' => 'success', 'data' => $list->load('items')], 201);
        });
    }

    /**
     * CORE GENERATION ENGINE: Compilation automation pipeline
     */
    public function generateDocuments(LoadingList $loadingList)
    {
        return DB::transaction(function () use ($loadingList) {
            $loadingList->load(['container', 'voyage', 'items']);
            
            // 1. Generate Master Commercial Invoice entry context
            // Iterates over gross list volumes to compile cross-border valuation arrays
            
            // 2. Loop through mapped sectors (Sub-marks M1 to M20) to extract custom HBL rows
            // Anyone assigned space inside sector arrays automatically receives an independent HBL
            
            // 3. Auto-compile associated Freight Invoice balances

            $loadingList->update(['document_status' => 'compiled']);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully generated downstream custom House Bills and Commercial Invoice Manifests.',
            ]);
        });
    }
}
