<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // ===== Stats ringkas (1 query pakai SUM boolean) =====
        $statsRow = Order::query()
            ->selectRaw("
                SUM(status = 'pending')         AS pending,
                SUM(status = 'in_production')   AS in_production,
                SUM(status = 'completed')       AS completed
            ")
            ->where('user_id', $user->id)
            ->first();

        $stats = [
            'pending'        => (int) ($statsRow->pending ?? 0),
            'in_production'  => (int) ($statsRow->in_production ?? 0),
            'completed'      => (int) ($statsRow->completed ?? 0),
        ];

        // ===== Pesanan terbaru (5 item terakhir) =====
        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(5)
            ->get(['id','code','status','total_amount','created_at'])
            ->map(fn ($o) => [
                'id'         => $o->id,
                'code'       => $o->code,
                'status'     => $o->status,           // pending|paid|in_production|completed|cancelled
                'total'      => $o->total_amount,
                'created_at' => $o->created_at->toDateTimeString(),
                'show_url'   => route('orders.show', $o->code),
            ]);

        // ===== Prefill profil sederhana (aman untuk null) =====
        // $profileModel = $user->profile ?? null;
        // $profile = [
        //     'full_name' => $profileModel->full_name ?? $user->name ?? null,
        //     'phone'     => $profileModel->phone     ?? null,
        //     // Komponen kamu mengakses address.street → kita bungkus di object
        //     'address'   => [
        //         'street' => $profileModel->address_json ?? null,
        //     ],
        // ];

        $profile = Profile::firstOrCreate(['user_id' => $user->id], [
            'full_name' => $user->name ?? null,
            'phone'     => $user->phone ?? null,
            'address' => [
                'street' => $user->address_json ?? null,
            ],
        ]);

        return Inertia::render('Customer/Dashboard', [
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
            'profile'      => $profile,
        ]);
    }
}