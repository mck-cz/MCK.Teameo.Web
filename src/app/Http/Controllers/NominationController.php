<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Nomination;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NominationController extends Controller
{
    public function manage(Event $event)
    {
        $clubId = session('current_club_id');

        // Verify event belongs to current club
        abort_unless($event->club_id === $clubId, 403);

        // Verify event type is match, tournament, or competition
        abort_unless(in_array($event->event_type, ['match', 'tournament', 'competition']), 403);

        $event->load(['team', 'nominations.teamMembership.user']);

        // Get all active team members
        $activeMembers = TeamMembership::where('team_id', $event->team_id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        // Filter out already nominated members
        $nominatedMemberIds = $event->nominations->pluck('team_membership_id')->toArray();
        $availableMembers = $activeMembers->filter(
            fn ($member) => !in_array($member->id, $nominatedMemberIds)
        );

        return view('events.nominations', [
            'event' => $event,
            'activeMembers' => $activeMembers,
            'availableMembers' => $availableMembers,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $clubId = session('current_club_id');

        // Verify event belongs to current club
        abort_unless($event->club_id === $clubId, 403);

        $validated = $request->validate([
            'team_membership_ids' => 'required|array',
            'team_membership_ids.*' => 'exists:team_memberships,id',
        ]);

        foreach ($validated['team_membership_ids'] as $membershipId) {
            // Skip if nomination already exists for that member
            $exists = Nomination::where('event_id', $event->id)
                ->where('team_membership_id', $membershipId)
                ->exists();

            if (!$exists) {
                Nomination::create([
                    'event_id' => $event->id,
                    'team_membership_id' => $membershipId,
                    'source_team_id' => $event->team_id,
                    'status' => 'nominated',
                    'priority' => 1,
                    'nominated_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->back()->with('success', __('messages.nominations.added'));
    }

    public function respond(Request $request, Nomination $nomination)
    {
        $user = Auth::user();

        // Verify the nomination belongs to the current user or user is a guardian
        $nominationUserId = $nomination->teamMembership->user_id;
        $isOwner = $nominationUserId === $user->id;
        $isGuardian = $user->guardianOf()->where('child_id', $nominationUserId)->exists();

        abort_unless($isOwner || $isGuardian, 403);

        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $nomination->update([
            'status' => $validated['status'],
            'responded_by' => Auth::id(),
            'responded_at' => now(),
        ]);

        return redirect()->back()->with('success', __('messages.nominations.updated'));
    }

    public function destroy(Nomination $nomination)
    {
        $clubId = session('current_club_id');

        // Verify belongs to current club through event
        abort_unless($nomination->event->club_id === $clubId, 403);

        $nomination->delete();

        return redirect()->back()->with('success', __('messages.nominations.removed'));
    }
}
