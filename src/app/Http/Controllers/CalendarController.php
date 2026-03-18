<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TeamMembership;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $clubId = session('current_club_id');

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get start of calendar grid (Monday of the week containing the 1st)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        // Get end of calendar grid (Sunday of the week containing the last day)
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Get user's teams
        $userTeamIds = TeamMembership::where('user_id', auth()->id())
            ->where('status', 'active')
            ->pluck('team_id');

        // Get events for the calendar range
        $events = Event::where('club_id', $clubId)
            ->whereIn('team_id', $userTeamIds)
            ->where('starts_at', '>=', $calendarStart)
            ->where('starts_at', '<=', $calendarEnd)
            ->where('status', '!=', 'cancelled')
            ->with('team', 'venue')
            ->orderBy('starts_at')
            ->get();

        // Group events by date
        $eventsByDate = $events->groupBy(fn ($event) => $event->starts_at->format('Y-m-d'));

        $prevMonth = $startOfMonth->copy()->subMonth();
        $nextMonth = $startOfMonth->copy()->addMonth();

        return view('calendar.index', compact(
            'startOfMonth',
            'endOfMonth',
            'calendarStart',
            'calendarEnd',
            'eventsByDate',
            'prevMonth',
            'nextMonth',
            'month',
            'year',
        ));
    }
}
