<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Http\Requests\Admin\StoreOrUpdateContainerRequest;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
 public function index(Request $request)
    {
        // Hydrate all relationships
        $query = Container::with(['voyage.vessel', 'voyage.party', 'transitor', 'shipper', 'batch']);

        // 1. Text Search Filter (Container #, Booking, Seal)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('container_number', 'LIKE', "%{$search}%")
                  ->orWhere('booking_number', 'LIKE', "%{$search}%")
                  ->orWhere('seal_number', 'LIKE', "%{$search}%");
            });
        }

        // 2. Base Filters (Status & Container Size)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('container_size')) {
            $query->where('container_size', $request->container_size);
        }

        // 3. Direct Relationship Filters (Shipper & Voyage)
        if ($request->filled('shipper_id')) {
            $query->where('shipper_id', $request->shipper_id);
        }

        if ($request->filled('voyage_id')) {
            $query->where('voyage_id', $request->voyage_id);
        }

        // 4. Nested Relationship Filter (Shipping Line via Voyage)
        if ($request->filled('shipping_line_id')) {
            $query->whereHas('voyage', function ($q) use ($request) {
                $q->where('party_id', $request->shipping_line_id);
            });
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function store(StoreOrUpdateContainerRequest $request)
    {
        $container = Container::create($request->validated());
        return response()->json(['status' => 'success', 'data' => $container->load(['voyage.vessel', 'transitor', 'shipper', 'batch'])], 201);
    }

    public function update(StoreOrUpdateContainerRequest $request, Container $container)
    {
        $container->update($request->validated());
        return response()->json(['status' => 'success', 'data' => $container->load(['voyage.vessel', 'transitor', 'shipper', 'batch'])]);
    }

    public function destroy(Container $container)
    {
        $container->delete();
        return response()->json(['status' => 'success']);
    }
}
