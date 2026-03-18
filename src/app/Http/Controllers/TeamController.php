<?php

namespace App\Http\Controllers;

use App\Models\ClubMembership;
use App\Models\Season;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Display a listing of the user's teams.
     */
    public function index()
    {
        $clubId = session('current_club_id');
        $userId = Auth::id();

        // Club admins/owners see all teams, members see only their own
        $isClubAdmin = \App\Models\ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        $query = Team::where('club_id', $clubId);

        if (!$isClubAdmin) {
            $query->whereHas('teamMemberships', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where('status', 'active');
            });
        }

        $teams = $query->withCount(['teamMemberships' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('name')
            ->get();

        return view('teams.index', compact('teams'));
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $clubId = session('current_club_id');

        abort_unless($team->club_id === $clubId, 404);

        $team->load([
            'teamMemberships' => function ($query) {
                $query->where('status', 'active')
                    ->with('user')
                    ->orderByRaw("CASE role WHEN 'head_coach' THEN 1 WHEN 'assistant_coach' THEN 2 WHEN 'athlete' THEN 3 ELSE 4 END")
                    ->orderBy('joined_at');
            },
        ]);

        $upcomingEvents = $team->events()
            ->upcoming()
            ->with('venue')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $userId = Auth::id();

        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        $isHeadCoach = TeamMembership::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->where('role', 'head_coach')
            ->where('status', 'active')
            ->exists();

        $canEdit = $isClubAdmin || $isHeadCoach;
        $canDelete = $isClubAdmin;

        return view('teams.show', compact('team', 'upcomingEvents', 'canEdit', 'canDelete'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        $clubId = session('current_club_id');

        $seasons = Season::where('club_id', $clubId)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('teams.create', compact('seasons'));
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $clubId = session('current_club_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sport' => 'nullable|string|max:255',
            'age_category' => 'nullable|string|max:255',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        $team = Team::create([
            ...$validated,
            'club_id' => $clubId,
            'is_active' => true,
            'is_archived' => false,
        ]);

        TeamMembership::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'role' => 'head_coach',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return redirect()->route('teams.show', $team)
            ->with('success', __('messages.teams.created'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        $this->authorizeTeamEdit($team);

        $seasons = Season::where('club_id', $clubId)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('teams.edit', compact('team', 'seasons'));
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        $this->authorizeTeamEdit($team);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sport' => 'nullable|string|max:255',
            'age_category' => 'nullable|string|max:255',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        $team->update($validated);

        return redirect()->route('teams.show', $team)
            ->with('success', __('messages.teams.updated'));
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        $this->authorizeClubAdmin();

        // Delete related data
        foreach ($team->events as $event) {
            $event->attendances()->delete();
            $event->nominations()->delete();
            $event->delete();
        }
        $team->teamMemberships()->delete();
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', __('messages.teams.deleted'));
    }

    /**
     * Check if user can edit the team (owner, admin, or head_coach of this team).
     */
    private function authorizeTeamEdit(Team $team): void
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

        $isHeadCoach = TeamMembership::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->where('role', 'head_coach')
            ->where('status', 'active')
            ->exists();

        abort_unless($isHeadCoach, 403);
    }

    /**
     * Check if user is club owner or admin.
     */
    private function authorizeClubAdmin(): void
    {
        $clubId = session('current_club_id');
        $userId = Auth::id();

        $isClubAdmin = ClubMembership::where('club_id', $clubId)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();

        abort_unless($isClubAdmin, 403);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        $this->authorizeTeamEdit($team);

        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:head_coach,assistant_coach,athlete',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            return back()->withErrors(['email' => __('messages.teams.user_not_found')]);
        }

        $alreadyMember = TeamMembership::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => __('messages.teams.already_member')]);
        }

        TeamMembership::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => $request->input('role'),
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return back()->with('success', __('messages.teams.member_added'));
    }

    /**
     * Update a member's role or position.
     */
    public function updateMember(Request $request, Team $team, TeamMembership $membership)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        abort_unless($membership->team_id === $team->id, 404);
        $this->authorizeTeamEdit($team);

        $validated = $request->validate([
            'role' => 'sometimes|in:head_coach,assistant_coach,athlete',
            'position' => 'nullable|string|max:100',
        ]);

        $membership->update($validated);

        return back()->with('success', __('messages.teams.member_updated'));
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, TeamMembership $membership)
    {
        $clubId = session('current_club_id');
        abort_unless($team->club_id === $clubId, 404);
        abort_unless($membership->team_id === $team->id, 404);
        $this->authorizeTeamEdit($team);

        $membership->delete();

        return back()->with('success', __('messages.teams.member_removed'));
    }
}
