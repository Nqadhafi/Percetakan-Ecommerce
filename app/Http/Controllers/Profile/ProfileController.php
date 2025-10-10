<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Profile;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id], [
            'full_name' => $user->name,
        ]);

        return Inertia::render('Profile/Index', [
            'profile' => [
                'id'         => $profile->id,
                'full_name'  => $profile->full_name,
                'phone'      => $profile->phone,
                'company'    => $profile->company,
                'tax_id'     => $profile->tax_id,
                'address'    => $profile->address_json,
                'updated_at' => $profile->updated_at?->toDateTimeString(),
            ],
            'user' => $user->only('id','name','email'),
        ]);
    }

    public function update(ProfileUpdateRequest $r)
    {
        $user = auth()->user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);

        $profile->update([
            'full_name'   => $r->input('full_name'),
            'phone'       => $r->input('phone'),
            'company'     => $r->input('company'),
            'tax_id'      => $r->input('tax_id'),
            'address_json'=> $r->input('address'),
        ]);

        return redirect()->route('profile.index')->with('success','Profil berhasil diperbarui.');
    }
}
