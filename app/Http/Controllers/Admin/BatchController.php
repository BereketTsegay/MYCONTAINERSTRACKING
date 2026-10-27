<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Http\Requests\Admin\StoreOrUpdateBatchRequest;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function store(StoreOrUpdateBatchRequest $request)
    {
        $batch = Batch::create($request->validated());
        return response()->json(['status' => 'success', 'data' => $batch], 201);
    }

    public function update(StoreOrUpdateBatchRequest $request, Batch $batch)
    {
        $batch->update($request->validated());
        return response()->json(['status' => 'success', 'data' => $batch]);
    }

    public function destroy(Batch $batch)
    {
        $batch->delete();
        return response()->json(['status' => 'success']);
    }
}
