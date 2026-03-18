@extends('layouts.app')

@section('title', __('messages.consents.title'))

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <h1 class="text-xl font-semibold mb-6">{{ __('messages.consents.title') }}</h1>

    {{-- User's consents --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="font-medium">{{ __('messages.consents.my_consents') }}</h2>
        </div>
        <div class="card-body">
            @forelse($consentTypes as $type)
                <div class="flex items-center justify-between py-3 @if(!$loop->last) border-b border-border @endif">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $type->name }}</span>
                            @if($type->is_required)
                                <span class="badge badge-warning">{{ __('messages.consents.required') }}</span>
                            @endif
                        </div>
                        @if($type->description)
                            <p class="text-sm text-muted mt-1">{{ $type->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        @php $consent = $userConsents[$type->id] ?? null; @endphp
                        @if($consent && $consent->granted)
                            <span class="text-success text-sm font-medium">&#10003; {{ __('messages.consents.granted_label') }}</span>
                            @if(!$type->is_required)
                                <form action="{{ route('consents.revoke', $type) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost text-sm text-danger">{{ __('messages.consents.revoke') }}</button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('consents.grant', $type) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn-primary text-sm">{{ __('messages.consents.grant') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">{{ __('messages.consents.no_types') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Admin: manage consent types --}}
    @php
        $isAdmin = \App\Models\ClubMembership::where('club_id', session('current_club_id'))
            ->where('user_id', auth()->id())
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();
    @endphp

    @if($isAdmin)
        <div class="card">
            <div class="card-header">
                <h2 class="font-medium">{{ __('messages.consents.manage_types') }}</h2>
            </div>
            <div class="card-body">
                @foreach($consentTypes as $type)
                    <div class="flex items-center justify-between py-2 @if(!$loop->last) border-b border-border @endif">
                        <div>
                            <span class="font-medium">{{ $type->name }}</span>
                            @if($type->is_required)
                                <span class="badge badge-warning text-xs">{{ __('messages.consents.required') }}</span>
                            @endif
                        </div>
                        <form action="{{ route('consent-types.destroy', $type) }}" method="POST"
                            onsubmit="return confirm('{{ __('messages.consents.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-muted hover:text-danger">{{ __('messages.common.delete') }}</button>
                        </form>
                    </div>
                @endforeach

                <form action="{{ route('consent-types.store') }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="name" class="form-label">{{ __('messages.consents.type_name') }}</label>
                        <input type="text" name="name" id="name" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="description" class="form-label">{{ __('messages.consents.description') }} <span class="text-muted">({{ __('messages.common.optional') }})</span></label>
                        <textarea name="description" id="description" rows="2" class="form-input w-full"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_required" id="is_required" value="1" class="form-checkbox">
                        <label for="is_required" class="text-sm">{{ __('messages.consents.is_required') }}</label>
                    </div>
                    <button type="submit" class="btn-primary text-sm">{{ __('messages.consents.add_type') }}</button>
                </form>
            </div>
        </div>
    @endif
@endsection
