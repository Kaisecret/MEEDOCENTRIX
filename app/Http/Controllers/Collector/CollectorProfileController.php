<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CollectorProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('collector.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ((string) $request->input('update_mode') === 'avatar') {
            $validated = $request->validate([
                'avatar' => ['required', Rule::in(['boy', 'girl'])],
            ]);

            $user->avatar = $validated['avatar'];
            $user->save();

            return redirect()
                ->route('collector.profile')
                ->with('status', 'Avatar updated successfully.');
        }

        if ((string) $request->input('update_mode') === 'availability') {
            if (! Schema::hasColumn('users', 'is_absent')) {
                return redirect()
                    ->route('collector.profile')
                    ->with('error', 'Collector availability column is missing. Please run database migrations first.');
            }

            $validated = $request->validate([
                'availability_state' => ['required', Rule::in(['available', 'absent'])],
            ]);

            $setAbsent = (string) $validated['availability_state'] === 'absent';

            $user->is_absent = $setAbsent;
            $user->absent_set_at = $setAbsent ? now() : null;
            $user->save();

            if (! $setAbsent && Schema::hasTable('app_notifications')) {
                AppNotification::query()
                    ->where('user_id', (int) $user->id)
                    ->where('event_key', 'like', 'collector_availability_reminder_%')
                    ->where('is_read', false)
                    ->update([
                        'is_read' => true,
                        'read_at' => now(),
                    ]);
            }

            return redirect()
                ->route('collector.profile')
                ->with('status', $setAbsent ? 'Status updated: You are now marked absent.' : 'Status updated: You are now available for assignment.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', Rule::in(['boy', 'girl'])],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'username' => $validated['username'] ?: null,
            'email' => $validated['email'],
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        $user->save();

        return redirect()
            ->route('collector.profile')
            ->with('status', 'Profile updated successfully.');
    }
}
