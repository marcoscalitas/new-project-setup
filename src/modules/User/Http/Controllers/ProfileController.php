<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController
{
    public function edit(Request $request): \Illuminate\View\View
    {
        $user = $request->user();
        return view('user::profile.edit', compact('user'));
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', __('ui.profile_updated'));
    }

    public function updatePassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($request->input('current_password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('ui.incorrect_password')],
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('profile.edit')->with('success', __('ui.password_updated'));
    }
}
