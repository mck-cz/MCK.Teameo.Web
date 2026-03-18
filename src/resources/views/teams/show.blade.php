@extends('layouts.app')

@section('title', $team->name)

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-error mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('teams.index') }}" class="text-sm text-muted hover:underline">
            &larr; {{ __('messages.common.back') }}
        </a>
    </div>

    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                @if($team->color)
                    <div class="w-5 h-5 rounded-full shrink-0" style="background-color: {{ $team->color }}"></div>
                @endif
                <h1 class="text-2xl font-semibold">{{ $team->name }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('teams.wall', $team) }}" class="btn-ghost text-sm">{{ __('messages.wall.title') }}</a>
                @if($canEdit ?? false)
                    <a href="{{ route('teams.edit', $team) }}" class="btn-secondary text-sm">{{ __('messages.common.edit') }}</a>
                    @if($canDelete ?? false)
                        <form action="{{ route('teams.destroy', $team) }}" method="POST"
                            onsubmit="return confirm('{{ __('messages.teams.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger text-sm">{{ __('messages.common.delete') }}</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($team->sport)
                <span class="badge badge-primary">{{ $team->sport }}</span>
            @endif
            @if($team->age_category)
                <span class="badge badge-gray">{{ $team->age_category }}</span>
            @endif
        </div>
    </div>

    {{-- Roster --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.teams.roster') }}</h2>
        </div>
        <div class="card-body">
            @if($team->teamMemberships->isEmpty())
                <p class="text-muted">{{ __('messages.teams.no_members') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="pb-2 font-medium">{{ __('messages.teams.members') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.teams.role') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.teams.position') }}</th>
                                <th class="pb-2 font-medium">{{ __('messages.teams.joined') }}</th>
                                <th class="pb-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($team->teamMemberships as $membership)
                                <tr class="border-b border-border last:border-0" x-data="{ editing: false }">
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                                {{ strtoupper(mb_substr($membership->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($membership->user->last_name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium">{{ $membership->user->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <template x-if="!editing">
                                            <div>
                                                @if($membership->role === 'head_coach')
                                                    <span class="badge badge-success">{{ __('messages.teams.head_coach') }}</span>
                                                @elseif($membership->role === 'assistant_coach')
                                                    <span class="badge badge-primary">{{ __('messages.teams.assistant_coach') }}</span>
                                                @else
                                                    <span class="badge badge-gray">{{ __('messages.teams.athlete') }}</span>
                                                @endif
                                            </div>
                                        </template>
                                        <template x-if="editing">
                                            <select name="role" form="edit-member-{{ $membership->id }}" class="form-select text-sm">
                                                <option value="athlete" @selected($membership->role === 'athlete')>{{ __('messages.teams.athlete') }}</option>
                                                <option value="assistant_coach" @selected($membership->role === 'assistant_coach')>{{ __('messages.teams.assistant_coach') }}</option>
                                                <option value="head_coach" @selected($membership->role === 'head_coach')>{{ __('messages.teams.head_coach') }}</option>
                                            </select>
                                        </template>
                                    </td>
                                    <td class="py-3">
                                        <template x-if="!editing">
                                            <span class="text-muted">{{ $membership->position ?? '—' }}</span>
                                        </template>
                                        <template x-if="editing">
                                            <input type="text" name="position" form="edit-member-{{ $membership->id }}"
                                                value="{{ $membership->position }}"
                                                class="form-input text-sm w-full" style="min-width: 100px;">
                                        </template>
                                    </td>
                                    <td class="py-3 text-muted">
                                        {{ $membership->joined_at ? (app()->getLocale() === 'cs' ? $membership->joined_at->format('d.m.Y') : $membership->joined_at->format('Y-m-d')) : '—' }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Edit toggle --}}
                                            <template x-if="!editing">
                                                <button type="button" @click="editing = true" class="btn-ghost text-sm" title="{{ __('messages.teams.edit_member') }}" aria-label="{{ __('messages.teams.edit_member') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </template>
                                            {{-- Save / Cancel --}}
                                            <template x-if="editing">
                                                <div class="flex items-center gap-1">
                                                    <form id="edit-member-{{ $membership->id }}" action="{{ route('teams.update-member', [$team, $membership]) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn-ghost text-success text-sm" title="{{ __('messages.common.save') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    <button type="button" @click="editing = false" class="btn-ghost text-muted text-sm" title="{{ __('messages.common.cancel') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            {{-- Remove --}}
                                            <template x-if="!editing">
                                                <form action="{{ route('teams.remove-member', [$team, $membership]) }}" method="POST"
                                                    onsubmit="return confirm('{{ __('messages.teams.remove_confirm') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-ghost text-danger" title="{{ __('messages.common.delete') }}" aria-label="{{ __('messages.common.delete') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Add Member --}}
    <div class="card mb-6" x-data="{ open: false }">
        <div class="card-header cursor-pointer flex items-center justify-between" @click="open = !open">
            <h2 class="font-medium">{{ __('messages.teams.add_member') }}</h2>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-muted transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
        <div class="card-body" x-show="open" x-cloak>
            <p class="text-sm text-muted mb-4">{{ __('messages.teams.add_member_desc') }}</p>

            <form action="{{ route('teams.add-member', $team) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="form-label">{{ __('messages.teams.email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="form-input @error('email') border-danger @enderror" required>
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="form-label">{{ __('messages.teams.role') }}</label>
                    <select name="role" id="role" class="form-select @error('role') border-danger @enderror" required>
                        <option value="athlete" @selected(old('role') === 'athlete')>{{ __('messages.teams.athlete') }}</option>
                        <option value="assistant_coach" @selected(old('role') === 'assistant_coach')>{{ __('messages.teams.assistant_coach') }}</option>
                        <option value="head_coach" @selected(old('role') === 'head_coach')>{{ __('messages.teams.head_coach') }}</option>
                    </select>
                    @error('role')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit" class="btn-primary">{{ __('messages.teams.add') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Upcoming Events --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.teams.upcoming_events') }}</h2>
        </div>
        <div class="card-body">
            @if($upcomingEvents->isEmpty())
                <p class="text-muted">{{ __('messages.teams.no_upcoming_events') }}</p>
            @else
                <div class="space-y-3">
                    @foreach($upcomingEvents as $event)
                        <a href="{{ route('events.show', $event) }}" class="flex items-start gap-4 py-2 border-b border-border last:border-0 hover:bg-bg rounded-lg transition-colors">
                            <div class="text-center shrink-0">
                                <div class="text-xs text-muted">{{ app()->getLocale() === 'cs' ? $event->starts_at->format('d.m.') : $event->starts_at->format('M d') }}</div>
                                <div class="text-sm font-medium">{{ $event->starts_at->format('H:i') }}</div>
                            </div>
                            <div>
                                <div class="font-medium">{{ $event->title }}</div>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span class="badge badge-gray">{{ __('messages.events.' . $event->event_type) }}</span>
                                    @if($event->venue)
                                        <span class="text-sm text-muted">{{ $event->venue->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
