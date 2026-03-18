@extends('layouts.app')

@section('title', __('messages.nominations.title') . ' — ' . $event->title)

@section('content')
    {{-- Back link --}}
    <div class="mb-4">
        <a href="{{ route('events.show', $event) }}" class="text-sm text-muted hover:underline">&larr; {{ __('messages.common.back') }}</a>
    </div>

    {{-- Page title --}}
    <div class="mb-6">
        <h1 class="text-xl font-semibold">{{ __('messages.nominations.title') }} — {{ $event->title }}</h1>
    </div>

    {{-- Current nominations --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.nominations.title') }}</h2>
        </div>
        <div class="card-body">
            @forelse ($event->nominations as $nomination)
                <div class="flex items-center gap-3 py-2 @if(!$loop->last) border-b border-border @endif">
                    <div class="flex-1">
                        <span class="font-medium">{{ $nomination->teamMembership->user->full_name }}</span>
                    </div>

                    @if($nomination->status === 'nominated')
                        <span class="badge badge-warning">{{ __('messages.nominations.nominated') }}</span>
                    @elseif($nomination->status === 'accepted')
                        <span class="badge badge-success">{{ __('messages.nominations.accepted') }}</span>
                    @elseif($nomination->status === 'declined')
                        <span class="badge badge-danger">{{ __('messages.nominations.declined') }}</span>
                    @endif

                    <form method="POST" action="{{ route('nominations.destroy', $nomination) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-ghost text-sm text-danger" aria-label="{{ __('messages.common.delete') }}">&times;</button>
                    </form>
                </div>
            @empty
                <p class="text-muted">{{ __('messages.nominations.no_nominations') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Add nominations --}}
    <div class="card">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.nominations.available_players') }}</h2>
        </div>
        <div class="card-body">
            @if($availableMembers->count() > 0)
                <form method="POST" action="{{ route('nominations.store', $event) }}">
                    @csrf

                    <div class="space-y-2 mb-4">
                        @foreach ($availableMembers as $member)
                            <label class="flex items-center gap-3 py-1">
                                <input type="checkbox" name="team_membership_ids[]" value="{{ $member->id }}" class="form-checkbox">
                                <span>{{ $member->user->full_name }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('team_membership_ids')
                        <p class="form-error mb-3">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn-primary">{{ __('messages.nominations.nominate') }}</button>
                </form>
            @else
                <p class="text-muted">{{ __('messages.nominations.no_available') }}</p>
            @endif
        </div>
    </div>
@endsection
