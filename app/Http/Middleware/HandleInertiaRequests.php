<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => fn () => $request->user()
                ? $request->user()->only('id','name','email')
                : null,
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error'   => fn () => $request->session()->get('error'),
        ],
        'cartCount' => function () use ($request) {
            // Hitung item di cart draft untuk user / session saat ini
            $query = \App\Models\Cart::query()->where('status','draft');
            if ($u = $request->user()) {
                $query->where('user_id', $u->id);
            } else {
                $query->where('session_id', $request->session()->getId());
            }
            $cartId = optional($query->select('id')->first())->id;
            if (!$cartId) return 0;
            return \App\Models\CartItem::where('cart_id',$cartId)->sum('qty');
        },
        'categories' => fn () => [
            ['name' => 'Print On Paper', 'href' => route('pop.index')],
            ['name' => 'MMT & Banner',   'href' => route('mmt.index')],
        ],
        'appName' => config('app.name'),
    ]);
    }
}
