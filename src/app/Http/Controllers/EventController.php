<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        // Get user's team IDs in this club
        $teamIds = TeamMembership::where('user_id', $user->id)
            ->whereHas('team', fn ($q) => $q->where('club_id', $clubId))
            ->pluck('team_id');

        // Build events query
        $query = Event::whereIn('team_id', $teamIds)
            ->where('status', 'scheduled')
            ->where('starts_at', '>', now())
            ->with(['team', 'venue', 'attendances'])
            ->orderBy('starts_at', 'asc');

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->ofType($request->input('event_type'));
        }

        // Filter by team
        if ($request->filled('team_id')) {
            $query->forTeam($request->input('team_id'));
        }

        $events = $query->get();

        // Get user's teams for filter dropdown
        $teams = Team::whereIn('id', $teamIds)->orderBy('name')->get();

        return view('events.index', [
            'events' => $events,
            'teams' => $teams,
            'selectedType' => $request->input('event_type'),
            'selectedTeamId' => $request->input('team_id'),
        ]);
    }

    public function show(Event $event)
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        // Verify event belongs to current club
        abort_unless($event->club_id === $clubId, 403);

        $event->load(['team', 'venue', 'attendances.teamMembership.user', 'nominations', 'eventComments.user', 'eventResult']);

        // Find current user's attendance record
        $userAttendance = $event->attendances
            ->first(fn ($attendance) => $attendance->teamMembership->user_id === $user->id);

        // Check if user can manage match results (coach or admin)
        $canManageResult = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists()
        || TeamMembership::where('team_id', $event->team_id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        return view('events.show', [
            'event' => $event,
            'userAttendance' => $userAttendance,
            'canManageResult' => $canManageResult,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        $teams = $this->getManageableTeams($user, $clubId);
        $venues = Venue::where('club_id', $clubId)->orderBy('name')->get();

        return view('events.create', [
            'teams' => $teams,
            'venues' => $venues,
        ]);
    }

    public function store(Request $request)
    {
        $clubId = session('current_club_id');

        // Verify user is a coach on the submitted team
        $user = Auth::user();
        $isCoach = TeamMembership::where('user_id', $user->id)
            ->where('team_id', $request->input('team_id'))
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();
        abort_unless($isCoach, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:training,match,competition,tournament',
            'team_id' => ['required', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('club_id', $clubId)],
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'nullable|date|after:starts_at',
            'rsvp_deadline' => 'nullable|date|before:starts_at',
            'min_capacity' => 'nullable|integer|min:1',
            'max_capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $event = Event::create([
            ...$validated,
            'club_id' => $clubId,
            'created_by' => Auth::id(),
            'status' => 'scheduled',
        ]);

        // Auto-create attendance records for all active team members
        $memberships = TeamMembership::where('team_id', $event->team_id)
            ->where('status', 'active')
            ->get();

        foreach ($memberships as $membership) {
            Attendance::create([
                'event_id' => $event->id,
                'team_membership_id' => $membership->id,
                'rsvp_status' => 'pending',
            ]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', __('messages.events.created'));
    }

    public function edit(Event $event)
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $teams = $this->getManageableTeams($user, $clubId);
        $venues = Venue::where('club_id', $clubId)->orderBy('name')->get();

        return view('events.edit', [
            'event' => $event,
            'teams' => $teams,
            'venues' => $venues,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:training,match,competition,tournament',
            'team_id' => ['required', Rule::exists('teams', 'id')->where('club_id', $clubId)],
            'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('club_id', $clubId)],
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'rsvp_deadline' => 'nullable|date|before:starts_at',
            'min_capacity' => 'nullable|integer|min:1',
            'max_capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', __('messages.events.updated'));
    }

    public function cancel(Event $event)
    {
        $clubId = session('current_club_id');

        abort_unless($event->club_id === $clubId, 403);
        $this->authorizeEventManage($event);

        $event->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return redirect()->route('events.index')
            ->with('success', __('messages.events.cancelled_msg'));
    }

    public function destroy(Event $event)
    {
        $clubId = session('current_club_id');
        abort_unless($event->club_id === $clubId, 403);

        $userId = Auth::id();
        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        $isCoach = TeamMembership::where('team_id', $event->team_id)
            ->where('user_id', $userId)
            ->where('role', 'head_coach')
            ->where('status', 'active')
            ->exists();

        abort_unless($isClubAdmin || $isCoach, 403);

        $event->attendances()->delete();
        $event->nominations()->delete();
        $event->eventComments()->delete();
        $event->eventResult()?->delete();
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', __('messages.events.deleted'));
    }

    private function authorizeEventManage(Event $event): void
    {
        $clubId = session('current_club_id');
        $userId = Auth::id();

        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isClubAdmin) {
            return;
        }

        $isCoach = TeamMembership::where('team_id', $event->team_id)
            ->where('user_id', $userId)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isCoach, 403);
    }

    private function getManageableTeams($user, $clubId)
    {
        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        if ($isClubAdmin) {
            return Team::where('club_id', $clubId)->orderBy('name')->get();
        }

        return Team::where('club_id', $clubId)
            ->whereHas('teamMemberships', fn ($q) => $q->where('user_id', $user->id)
                ->whereIn('role', ['head_coach', 'assistant_coach']))
            ->orderBy('name')
            ->get();
    }
}
