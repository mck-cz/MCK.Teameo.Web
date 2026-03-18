@extends('layouts.app')

@section('title', __('messages.dashboard.title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.dashboard.welcome') }}, {{ Auth::user()->full_name }}</h1>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <p class="text-3xl font-semibold">{{ $upcomingEvents->count() }}</p>
                <p class="text-sm text-muted mt-1">{{ __('messages.dashboard.events_count', ['count' => $upcomingEvents->count()]) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-3xl font-semibold">{{ $teams->count() }}</p>
                <p class="text-sm text-muted mt-1">{{ __('messages.dashboard.teams_count', ['count' => $teams->count()]) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-3xl font-semibold">{{ $pendingAttendances->count() }}</p>
                <p class="text-sm text-muted mt-1">{{ __('messages.dashboard.pending_count', ['count' => $pendingAttendances->count()]) }}</p>
            </div>
        </div>
        <a href="{{ route('notifications.index') }}" class="card hover:bg-bg transition-colors">
            <div class="card-body">
                <p class="text-3xl font-semibold">{{ $unreadCount }}</p>
                <p class="text-sm text-muted mt-1">{{ __('messages.dashboard.unread_notifications') }}</p>
            </div>
        </a>
    </div>

    {{-- Coach quick actions --}}
    @if($isCoach)
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="font-medium">{{ __('messages.dashboard.quick_actions') }}</h3>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('events.create') }}" class="btn-primary text-sm">{{ __('messages.events.create') }}</a>
                    <a href="{{ route('statistics.index') }}" class="btn-secondary text-sm">{{ __('messages.statistics.title') }}</a>
                    <a href="{{ route('recurrence-rules.index') }}" class="btn-secondary text-sm">{{ __('messages.recurrence.title') }}</a>
                </div>
            </div>
        </div>
    @endif

    {{-- Pending payments --}}
    @if($pendingPayments->isNotEmpty())
        <div class="card mb-6">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-medium">{{ __('messages.dashboard.pending_payments') }}</h3>
                <a href="{{ route('payments.index') }}" class="text-sm text-primary hover:underline">{{ __('messages.dashboard.view_all') }}</a>
            </div>
            <div class="card-body">
                @foreach($pendingPayments as $payment)
                    <div class="flex items-center justify-between {{ !$loop->last ? 'mb-3 pb-3 border-b border-border' : '' }}">
                        <div>
                            <p class="font-medium">{{ $payment->paymentRequest->name }}</p>
                            <p class="text-sm text-muted">
                                {{ number_format($payment->paymentRequest->amount, 0) }} {{ $payment->paymentRequest->currency ?? 'CZK' }}
                                @if($payment->status === 'overdue')
                                    <span class="text-danger font-medium">· {{ __('messages.payments.overdue') }}</span>
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('payments.show', $payment->paymentRequest) }}" class="btn-ghost text-sm">{{ __('messages.dashboard.view_all') }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Upcoming events --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-medium">{{ __('messages.dashboard.upcoming_events') }}</h3>
                <a href="{{ route('events.index') }}" class="text-sm text-primary hover:underline">{{ __('messages.dashboard.view_all') }}</a>
            </div>
            <div class="card-body">
                @forelse($upcomingEvents as $event)
                    <div class="flex items-start gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-border' : '' }}">
                        <div class="text-center shrink-0 w-12">
                            <p class="text-sm font-semibold">
                                {{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.') : $event->starts_at->format('M d') }}
                            </p>
                            <p class="text-xs text-muted">{{ $event->starts_at->format('H:i') }}</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium truncate">{{ $event->title }}</p>
                            <p class="text-sm text-muted truncate">
                                {{ $event->team->name }}
                                @if($event->venue)
                                    &middot; {{ __('messages.dashboard.event_at') }} {{ $event->venue->name }}
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0">
                            @switch($event->event_type)
                                @case('training')
                                    <span class="badge badge-primary">{{ __('messages.events.training') }}</span>
                                    @break
                                @case('match')
                                    <span class="badge badge-accent">{{ __('messages.events.match') }}</span>
                                    @break
                                @case('competition')
                                    <span class="badge badge-gray">{{ __('messages.events.competition') }}</span>
                                    @break
                                @case('tournament')
                                    <span class="badge badge-warning">{{ __('messages.events.tournament') }}</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-muted">{{ __('messages.dashboard.no_events') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-4">
            {{-- Pending responses --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-medium">{{ __('messages.dashboard.pending_responses') }}</h3>
                </div>
                <div class="card-body">
                    @forelse($pendingAttendances as $attendance)
                        <div class="flex items-center justify-between {{ !$loop->last ? 'mb-3 pb-3 border-b border-border' : '' }}">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium truncate">{{ $attendance->event->title }}</p>
                                <p class="text-sm text-muted">
                                    {{ app()->getLocale() === 'cs' ? $attendance->event->starts_at->format('d.m.') : $attendance->event->starts_at->format('M d') }}
                                    {{ $attendance->event->starts_at->format('H:i') }}
                                    &middot; {{ $attendance->event->team->name }}
                                </p>
                            </div>
                            <a href="{{ route('events.show', $attendance->event_id) }}" class="btn-primary text-xs px-3 py-1.5 shrink-0 ml-3">
                                {{ __('messages.dashboard.respond') }}
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-muted">{{ __('messages.dashboard.no_pending') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- My teams --}}
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-medium">{{ __('messages.dashboard.my_teams') }}</h3>
                    <a href="{{ route('teams.index') }}" class="text-sm text-primary hover:underline">{{ __('messages.dashboard.view_all') }}</a>
                </div>
                <div class="card-body">
                    @forelse($teams as $team)
                        <div class="flex items-center justify-between {{ !$loop->last ? 'mb-3 pb-3 border-b border-border' : '' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($team->color)
                                    <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $team->color }}"></div>
                                @endif
                                <p class="font-medium truncate">{{ $team->name }}</p>
                            </div>
                            <span class="text-sm text-muted shrink-0 ml-3">{{ $team->team_memberships_count }} {{ __('messages.teams.members') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-muted">{{ __('messages.common.no_results') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
