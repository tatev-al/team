<?php

namespace App\Http\Controllers;

use App\Models\Profession;
use Illuminate\Http\Request;
use App\Models\Detail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        return view('profile')
            ->with('user', auth()->user()->load(['professions', 'detail']))
            ->with('professions', Profession::all());
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => [
                'required',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'nullable|string',
        ]);

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password
        ]);

        return back()
            ->with('success', 'Profile successfully updated');
    }

    public function updateDetail(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $request->validate([
            'phone'       => 'required',
            'address'     => 'required|string|max:191',
            'city'        => 'required|string|max:191',
            'country'     => 'required|string|max:191'
        ]);

        Detail::updateOrCreate(
        ['user_id'    => $user->id],
        [
            'phone'   => $request->phone,
            'address' => $request->address,
            'city'    => $request->city,
            'country' => $request->country
        ]
        );

        $user->professions()->sync($request->profession);

        return back()
            ->with('success', 'Profile successfully updated');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('images/avatars'), $imageName);

        Detail::updateOrCreate(
            ['user_id'    => Auth::id()],
            [
                'avatar'  => $imageName
            ]
        );

        return back()
            ->with('success','Image successfully uploaded');
    }
}
