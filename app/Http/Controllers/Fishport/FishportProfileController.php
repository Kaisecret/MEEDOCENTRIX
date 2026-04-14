<?php

namespace App\Http\Controllers\Fishport;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FishportProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('fishport.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Avatar-only update flow so avatar save is never blocked by other profile fields.
        if ((string) $request->input('update_mode') === 'avatar') {
            $validated = $request->validate([
                'avatar' => ['required', Rule::in(['boy', 'girl'])],
            ]);

            $user->avatar = $validated['avatar'];
            $user->save();

            return redirect()
                ->route('fishport.profile')
                ->with('status', 'Avatar updated successfully.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar'   => ['nullable', Rule::in(['boy', 'girl'])],
        ]);

        $user->fill([
            'name'     => $validated['name'],
            'username' => $validated['username'] ?: null,
            'email'    => $validated['email'],
            'avatar'   => $validated['avatar'] ?? $user->avatar,
        ]);

        $user->save();

        return redirect()
            ->route('fishport.profile')
            ->with('status', 'Profile updated successfully.');
    }
}
