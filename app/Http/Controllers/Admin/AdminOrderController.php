<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOrderController extends Controller
{
    /**
     * List semua order, bisa difilter by status.
     * /admin/orders?status=pending
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Order::query()
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['pending','paid','in_production','completed','cancelled'])) {
            $query->where('status', $status);
        }

        // ambil subset kolom aja biar ringan
        $orders = $query->select([
                'id',
                'code',
                'customer_name',
                'customer_phone',
                'total_amount',
                'status',
                'payment_method',
                'created_at',
            ])
            ->paginate(20)
            ->through(function($o){
                return [
                    'id'             => $o->id,
                    'code'           => $o->code,
                    'customer_name'  => $o->customer_name,
                    'customer_phone' => $o->customer_phone,
                    'total_amount'   => $o->total_amount,
                    'status'         => $o->status,
                    'payment_method' => $o->payment_method,
                    'created_at'     => $o->created_at->toDateTimeString(),
                ];
            });

        // Buat counter kecil untuk tab (pending, in_production, completed, cancelled)
        // Biar admin bisa lihat "ada berapa antrian?"
        $counts = [
            'pending'        => Order::where('status','pending')->count(),
            'paid'           => Order::where('status','paid')->count(),
            'in_production'  => Order::where('status','in_production')->count(),
            'completed'      => Order::where('status','completed')->count(),
            'cancelled'      => Order::where('status','cancelled')->count(),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'filters' => [
                'status' => $status ?: null,
            ],
            'counts' => $counts,
            'orders' => $orders,
        ]);
    }

    /**
     * Detail satu order
     */
    public function show(string $code)
    {
        $order = Order::where('code', $code)->firstOrFail();

        $items = OrderItem::where('order_id', $order->id)
            ->orderBy('id')
            ->get()
            ->map(function($it){
                return [
                    'id'                => $it->id,
                    'product_type'      => $it->product_type,
                    'product_name'      => $it->product_name,
                    'qty'               => $it->qty,
                    'unit_price'        => $it->unit_price,
                    'total_price'       => $it->total_price,
                    'note'              => $it->note,
                    'spec_snapshot'     => $it->spec_snapshot,
                    'pricing_breakdown' => $it->pricing_breakdown,
                ];
            });

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'code'               => $order->code,
                'status'             => $order->status,
                'payment_method'     => $order->payment_method,
                'payment_proof_url'  => $order->payment_proof_url ?? null, // pastikan accessor ada di model Order
                'created_at'         => $order->created_at->toDateTimeString(),
                'customer_name'      => $order->customer_name,
                'customer_phone'     => $order->customer_phone,
                'customer_address'   => $order->customer_address,
                'subtotal_amount'    => $order->subtotal_amount,
                'adjustment_amount'  => $order->adjustment_amount,
                'total_amount'       => $order->total_amount,
                'meta'               => $order->meta,
                'payment_note'       => $order->meta['payment_note'] ?? null,
            ],
            'items' => $items,
            'status_options' => [
                'pending',
                'paid',
                'in_production',
                'completed',
                'cancelled',
            ],
        ]);
    }

    /**
     * Update status pesanan dari panel admin/staff
     * PATCH /admin/orders/{code}/status
     * body: { status: "in_production" }
     */
    public function updateStatus(Request $request, string $code)
    {
        $request->validate([
            'status' => ['required','in:pending,paid,in_production,completed,cancelled'],
        ]);

        $order = Order::where('code', $code)->firstOrFail();
        $order->status = $request->input('status');
        $order->save();

        return redirect()
            ->route('admin.orders.show', $order->code)
            ->with('success', 'Status pesanan diperbarui.');
    }
}
