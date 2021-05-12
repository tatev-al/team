<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\Avatar;
use App\Models\Profession;
use Illuminate\Http\Request;
use App\Models\Detail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            ->with('user', auth()->user()->load(['professions', 'detail', 'avatar']))
            ->with('professions', Profession::all());
    }

    public function updateProfile(ProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password
        ]);

        return back()
            ->with('success', 'Profile successfully updated');
    }

    public function updateDetail(DetailRequest $request): RedirectResponse
    {
        $user = auth()->user();

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
        $user = auth()->user();
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($user->load(['avatar'])->avatar) {
            Storage::delete($user->load(['avatar'])->avatar->path);
        }

        $file = $request->file('image')->store('avatars');

        if(Storage::exists($file)) {
            Avatar::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'original_name' => $request->file('image')->getClientOriginalName(),
                    'path' => $file
                ]
            );
        }

        return back()
            ->with('success','Image successfully uploaded');
    }
}
