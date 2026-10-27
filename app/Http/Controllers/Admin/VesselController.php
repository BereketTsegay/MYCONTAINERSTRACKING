<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Http\Requests\Admin\StoreOrUpdateVesselRequest;
use Illuminate\Http\Request;

class VesselController extends Controller
{
    public function index(Request $request)
    {
        $query = Vessel::with('party:id,name');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('imo_number', 'LIKE', "%{$search}%")
                  ->orWhere('call_sign', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function store(StoreOrUpdateVesselRequest $request)
    {
        $vessel = Vessel::create($request->validated());
        return response()->json(['status' => 'success', 'data' => $vessel->load('party:id,name')], 201);
    }

    public function update(StoreOrUpdateVesselRequest $request, Vessel $vessel)
    {
        $vessel->update($request->validated());
        return response()->json(['status' => 'success', 'data' => $vessel->load('party:id,name')]);
    }

    public function destroy(Vessel $vessel)
    {
        $vessel->delete();
        return response()->json(['status' => 'success']);
    }
}
