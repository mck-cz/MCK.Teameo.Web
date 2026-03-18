@extends('layouts.auth')

@section('title', __('messages.onboarding.title'))

@section('content')
    <div>
        <h1 class="text-xl font-semibold text-text text-center mb-1">{{ __('messages.onboarding.title') }}</h1>
        <p class="text-text-secondary text-center mb-6">{{ __('messages.onboarding.subtitle') }}</p>

        @if (session('success'))
            <div class="alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error mb-4">{{ session('error') }}</div>
        @endif

        <div class="space-y-3">
            {{-- Create a club --}}
            <a href="{{ route('onboarding.create-club') }}" class="card block hover:border-primary transition-colors">
                <div class="card-body flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-primary-light flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.create_club') }}</h2>
                        <p class="text-text-secondary text-sm">{{ __('messages.onboarding.create_club_desc') }}</p>
                    </div>
                </div>
            </a>

            {{-- Join a club --}}
            <a href="{{ route('onboarding.join-club') }}" class="card block hover:border-primary transition-colors">
                <div class="card-body flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-accent-light flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-text mb-1">{{ __('messages.onboarding.join_club') }}</h2>
                        <p class="text-text-secondary text-sm">{{ __('messages.onboarding.join_club_desc') }}</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-text-secondary hover:text-text text-sm">
                    {{ __('messages.nav.logout') }}
                </button>
            </form>
        </div>
    </div>
@endsection
