<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreOrUpdatePartyRequest;

class PartyController extends Controller
{
    public function index(Request $request)
    {
        $query = Party::query();

        // Optimized Search Engine matching name, email, phone or filter types
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function store(StoreOrUpdatePartyRequest $request)
    {
        $data = $request->validated();

        $party = Party::create($data);
        return response()->json(['status' => 'success', 'data' => $party], 201);
    }

    public function update(StoreOrUpdatePartyRequest $request, Party $party)
    {
        $data = $request->validated();


        $party->update($data);
        return response()->json(['status' => 'success', 'data' => $party]);
    }

    public function destroy(Party $party)
    {
        $party->delete();
        return response()->json(['status' => 'success']);
    }
}
