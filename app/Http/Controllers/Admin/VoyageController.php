<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voyage;
use App\Http\Requests\Admin\StoreOrUpdateVoyageRequest;
use Illuminate\Http\Request;

class VoyageController extends Controller
{

public function index(Request $request)
{
    $query = Voyage::with(['vessel', 'party', 'batch']);

    // 1. Text Search Filter (Global Overrides)
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('voyage_number', 'LIKE', "%{$search}%")
              ->orWhere('origin_port', 'LIKE', "%{$search}%")
              ->orWhere('destination_port', 'LIKE', "%{$search}%");
        });
    }

    // 2. Strict Relational Drops Filter
    if ($request->filled('vessel_id')) {
        $query->where('vessel_id', $request->vessel_id);
    }

    if ($request->filled('batch_id')) {
        $query->where('batch_id', $request->batch_id);
    }

    // 3. Status Timeline Filters (Calculated from active date: 2026-10-05)
    if ($request->filled('status')) {
        $currentDate = '2026-10-05'; // Anchored System Clock Time

        if ($request->status === 'completed') {
            $query->where('arrival_date', '<', $currentDate);
        } elseif ($request->status === 'transit') {
            $query->where('departure_date', '<=', $currentDate)
                  ->where('arrival_date', '>=', $currentDate);
        } elseif ($request->status === 'pending') {
            $query->where('departure_date', '>', $currentDate);
        }
    }

    return response()->json($query->latest()->paginate(10));
}


    public function store(StoreOrUpdateVoyageRequest $request)
    {
        $voyage = Voyage::create($request->validated());
        return response()->json(['status' => 'success', 'data' => $voyage->load(['vessel', 'party', 'batch'])], 201);
    }

    public function update(StoreOrUpdateVoyageRequest $request, Voyage $voyage)
    {
        $voyage->update($request->validated());
        return response()->json(['status' => 'success', 'data' => $voyage->load(['vessel', 'party', 'batch'])]);
    }

    public function destroy(Voyage $voyage)
    {
        $voyage->delete();
        return response()->json(['status' => 'success']);
    }
}
