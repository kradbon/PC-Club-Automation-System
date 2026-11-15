<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'preferred_locale' => ['required', 'in:en,ru,tj'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user();
        unset($validated['avatar']);

        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar')->store('avatars', 'public');
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $validated['avatar_path'] = $newAvatar;
        }

        $user->update($validated);

        return redirect()->route($this->profileRoute($user))->with('success', __('ui.profile_saved'));
    }

    private function profileRoute($user): string
    {
        return match ($user->role) {
            'admin', 'administrator' => 'admin.profile',
            'staff' => 'staff.profile',
            default => 'account.profile',
        };
    }
}
