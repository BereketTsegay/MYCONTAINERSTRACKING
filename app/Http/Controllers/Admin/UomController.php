<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;

class UomController extends Controller
{
    public function index() { return response()->json(UnitOfMeasurement::latest()->get()); }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:units_of_measurement,code'
        ]);
        $uom = UnitOfMeasurement::create($data);
        return response()->json(['status' => 'success', 'data' => $uom], 201);
    }

    public function destroy(UnitOfMeasurement $unitofmeasurement) {
        $unitofmeasurement->delete(); return response()->json(['status' => 'success']);
    }
}
