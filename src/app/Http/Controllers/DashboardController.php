<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClubMembership;
use App\Models\Event;
use App\Models\MemberPayment;
use App\Models\Notification;
use App\Models\TeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $clubId = session('current_club_id');

        // Get user's team IDs in this club
        $teamIds = TeamMembership::where('user_id', $user->id)
            ->whereHas('team', fn ($q) => $q->where('club_id', $clubId))
            ->pluck('team_id');

        // Get user's team membership IDs (needed for attendance lookups)
        $teamMembershipIds = TeamMembership::where('user_id', $user->id)
            ->whereIn('team_id', $teamIds)
            ->pluck('id');

        // Upcoming events (next 7 days) for user's teams
        $upcomingEvents = Event::whereIn('team_id', $teamIds)
            ->where('status', 'scheduled')
            ->where('starts_at', '>', now())
            ->where('starts_at', '<=', now()->addDays(7))
            ->with(['team', 'venue'])
            ->orderBy('starts_at')
            ->get();

        // Pending RSVP responses for the user
        $pendingAttendances = Attendance::whereIn('team_membership_id', $teamMembershipIds)
            ->where('rsvp_status', 'pending')
            ->whereHas('event', fn ($q) => $q->where('status', 'scheduled')->where('starts_at', '>', now()))
            ->with(['event.team', 'event.venue'])
            ->get()
            ->sortBy('event.starts_at');

        // User's teams in this club with member counts
        $teams = $user->teams()
            ->where('teams.club_id', $clubId)
            ->withCount('teamMemberships')
            ->get();

        // Pending payments for user
        $pendingPayments = MemberPayment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->with('paymentRequest')
            ->get();

        // Unread notifications count
        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Check if user is coach on any team
        $isCoach = TeamMembership::where('user_id', $user->id)
            ->whereIn('team_id', $teamIds)
            ->whereIn('role', ['head_coach', 'assistant_coach'])
            ->exists();

        return view('dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'pendingAttendances' => $pendingAttendances,
            'teams' => $teams,
            'pendingPayments' => $pendingPayments,
            'unreadCount' => $unreadCount,
            'isCoach' => $isCoach,
        ]);
    }
}
