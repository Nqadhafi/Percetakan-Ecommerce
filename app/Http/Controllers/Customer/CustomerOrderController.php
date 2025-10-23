<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerOrderController extends Controller
{
    public function index(Request $r)
    {
        $userId = $r->user()->id;
        $status = $r->query('status');
        $q      = trim((string)$r->query('q', ''));

        $orders = Order::query()
            ->where('user_id', $userId)
            ->when($status, fn($q2) => $q2->where('status', $status))
            ->when($q !== '', fn($q2) => $q2->where('code', 'like', "%{$q}%"))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn (Order $o) => [
                'code'       => $o->code,
                'status'     => $o->status,
                'total'      => $o->total_amount,
                'created_at' => $o->created_at->format('d/m/Y H:i'),
                'show_url'   => route('orders.show', $o->code),
            ])
            ->appends($r->only(['status','q']));

        return Inertia::render('Orders/Index', [
            'filters'       => ['status' => $status, 'q' => $q],
            'orders'        => $orders,
            'statusOptions' => [
                ['value'=>null,          'label'=>'Semua'],
                ['value'=>'pending',     'label'=>'Pending'],
                ['value'=>'paid',        'label'=>'Dibayar'],
                ['value'=>'in_production','label'=>'Diproduksi'],
                ['value'=>'completed',   'label'=>'Selesai'],
                ['value'=>'cancelled',   'label'=>'Dibatalkan'],
            ],
        ]);
    }

    public function show(Request $r, string $code)
    {
        $order = Order::where('code', $code)
            ->where('user_id', $r->user()->id)
            ->firstOrFail();

        return Inertia::render('Orders/Show', [
            'order' => [
                'code'              => $order->code,
                'status'            => $order->status,
                'subtotal'          => $order->subtotal_amount,
                'discount'          => $order->discount_amount,
                'shipping'          => $order->shipping_amount,
                'adjustment'        => $order->adjustment_amount,
                'total'             => $order->total_amount,
                'customer_name'     => $order->customer_name,
                'customer_phone'    => $order->customer_phone,
                'customer_address'  => $order->customer_address,
                'payment_method'    => $order->payment_method,
                'payment_proof_url' => $order->payment_proof_url,
                'created_at'        => $order->created_at->toDateTimeString(),
            ],
            'items' => $order->items()->get([
                    'product_name','qty','unit_price','total_price','spec_snapshot','note'
                ])->map(fn ($i) => [
                    'name'        => $i->product_name,
                    'qty'         => $i->qty,
                    'unit_price'  => $i->unit_price,
                    'total_price' => $i->total_price,
                    'spec'        => $i->spec_snapshot,
                    'note'        => $i->note,
                ]),
        ]);
    }
}