<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'rsvp_status' => ['required', 'in:confirmed,declined'],
            'rsvp_note' => ['nullable', 'string'],
        ]);

        // Verify the attendance belongs to the current user
        abort_unless(
            $attendance->teamMembership->user_id === Auth::id(),
            403
        );

        $attendance->update([
            'rsvp_status' => $validated['rsvp_status'],
            'rsvp_note' => $validated['rsvp_note'] ?? null,
            'responded_by' => Auth::id(),
            'responded_at' => now(),
        ]);

        return redirect()->back()->with('success', __('messages.events.response_updated'));
    }
}
