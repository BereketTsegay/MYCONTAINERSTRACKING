<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Container;
use App\Models\Party;
use App\Models\Product;
use App\Models\User;
use App\Models\Voyage;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminDashboardController extends Controller
{
    // --- USER MANAGEMENT ---
    public function usersIndex()
    {
        return response()->json(User::with('roles:name')->latest()->paginate(10));
    }

    public function userStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        activity()->performedOn($user)->log('Created a new system user account');

        return response()->json(['status' => 'success', 'user' => $user], 201);
    }

    // --- SYSTEM ACTIVITY LOGS ---
    public function activityIndex()
    {
        // Pull latest 50 system activities with the user who performed them
        $logs = Activity::with('causer')
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'causer' => $log->causer ? $log->causer->name : 'System/Guest',
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            });

        return response()->json($logs);
    }

    /**
     * Get real-time metric aggregates for the dashboard view.
     */
    public function metrics(): JsonResponse
    {
        // 1. Calculate split product category type summary counts
        $categoryCounts = Category::select('type', \DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // 2. Compile metrics package payload
        return response()->json([
            'status' => 'success',
            'data' => [
                'categories' => [
                    'pklist' => $categoryCounts['pklist'] ?? 0,
                    'house_bill' => $categoryCounts['house_bill'] ?? 0,
                    'edpass' => $categoryCounts['edpass'] ?? 0,
                ],
                'totals' => [
                    'products' => Product::count(),
                    'containers' => Container::count(),
                    'voyages' => Voyage::count(),
                    'parties' => Party::count(),
                ]
            ]
        ]);
    }

}
