<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    public function store(Request $request)
{
    $data = $request->validate([
        'address1'     => ['required', 'string', 'max:255'],
        'address2'     => ['nullable', 'string', 'max:255'],
        'city'         => ['required', 'string', 'max:255'],
        'state'        => ['nullable', 'string', 'max:45'],
        'zipcode'      => ['required', 'string', 'max:45'],
        'country_code' => ['required', 'string', 'size:2'],
        'type'         => ['required', 'in:home,work'],
        'is_main'      => ['boolean'],
    ]);

    $user = $request->user();

    DB::transaction(function () use ($user, $data, $request) {
        $isMain = $request->boolean('is_main') || ! $user->addresses()->exists();

        if ($isMain) {
            $user->addresses()->update(['is_main' => false]);
        }

        $user->addresses()->create([
            ...$data,
            'country_code' => strtoupper($data['country_code']),
            'is_main'      => $isMain,
        ]);
    });

    return back()->with('success', 'Address saved.');
}
}
