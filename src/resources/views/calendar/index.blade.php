@extends('layouts.app')

@section('title', __('messages.calendar.title'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">{{ __('messages.calendar.title') }}</h1>
    </div>

    {{-- Month navigation --}}
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('calendar.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
            class="btn-ghost text-sm">
            &larr; {{ app()->getLocale() === 'cs' ? $prevMonth->translatedFormat('F Y') : $prevMonth->format('F Y') }}
        </a>
        <h2 class="text-lg font-semibold">
            {{ app()->getLocale() === 'cs' ? $startOfMonth->translatedFormat('F Y') : $startOfMonth->format('F Y') }}
        </h2>
        <a href="{{ route('calendar.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
            class="btn-ghost text-sm">
            {{ app()->getLocale() === 'cs' ? $nextMonth->translatedFormat('F Y') : $nextMonth->format('F Y') }} &rarr;
        </a>
    </div>

    {{-- Calendar grid --}}
    <div class="card">
        <div class="card-body p-2 sm:p-4">
            {{-- Day headers --}}
            <div class="grid grid-cols-7 gap-px mb-1">
                @php
                    $dayNames = app()->getLocale() === 'cs'
                        ? ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne']
                        : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                @endphp
                @foreach($dayNames as $day)
                    <div class="text-center text-xs font-medium text-muted py-2">{{ $day }}</div>
                @endforeach
            </div>

            {{-- Day cells --}}
            <div class="grid grid-cols-7 gap-px">
                @php
                    $currentDate = $calendarStart->copy();
                @endphp
                @while($currentDate <= $calendarEnd)
                    @php
                        $dateKey = $currentDate->format('Y-m-d');
                        $dayEvents = $eventsByDate->get($dateKey, collect());
                        $isCurrentMonth = $currentDate->month === $month;
                        $isToday = $currentDate->isToday();
                    @endphp
                    <div class="min-h-[80px] sm:min-h-[100px] border border-border rounded-lg p-1 {{ !$isCurrentMonth ? 'opacity-40' : '' }} {{ $isToday ? 'bg-primary-light' : 'bg-surface' }}">
                        <div class="text-xs font-medium mb-1 {{ $isToday ? 'text-primary' : 'text-muted' }}">
                            {{ $currentDate->day }}
                        </div>
                        @foreach($dayEvents->take(3) as $event)
                            <a href="{{ route('events.show', $event) }}"
                                title="{{ $event->starts_at->format('H:i') }} {{ $event->title }}"
                                class="block text-xs truncate rounded px-1 py-0.5 mb-0.5 hover:opacity-80 transition-opacity
                                    @switch($event->event_type)
                                        @case('training') bg-primary-light text-primary @break
                                        @case('match') bg-accent-light text-accent @break
                                        @case('tournament') bg-warning-bg text-warning @break
                                        @default bg-gray-light text-text-secondary
                                    @endswitch">
                                {{ $event->starts_at->format('H:i') }} {{ $event->title }}
                            </a>
                        @endforeach
                        @if($dayEvents->count() > 3)
                            <div class="text-xs text-muted px-1">+{{ $dayEvents->count() - 3 }}</div>
                        @endif
                    </div>
                    @php
                        $currentDate->addDay();
                    @endphp
                @endwhile
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-4 mt-4 text-xs">
        <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-primary-light border border-primary/30"></div>
            <span class="text-muted">{{ __('messages.events.training') }}</span>
        </div>
        <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-accent-light border border-accent/30"></div>
            <span class="text-muted">{{ __('messages.events.match') }}</span>
        </div>
        <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-warning-bg border border-warning/30"></div>
            <span class="text-muted">{{ __('messages.events.tournament') }}</span>
        </div>
        <div class="flex items-center gap-1">
            <div class="w-3 h-3 rounded bg-gray-light border border-border"></div>
            <span class="text-muted">{{ __('messages.events.competition') }}</span>
        </div>
    </div>
@endsection
